/**
 * Conditions Builder UI
 *
 * jQuery-based admin interface for building conditions.
 *
 * @package ArrayPress\Conditions
 */

console.log('conditions.js loaded');

(function ($) {
    'use strict';

    // Configuration from PHP
    const config = window.conditionsData || {};
    const conditions = config.conditions || {};
    const operators = config.operators || {};
    const restUrl = config.restUrl || '';
    const nonce = config.nonce || '';
    const i18n = config.i18n || {};

    /**
     * Generate unique ID
     */
    function generateId() {
        return Math.random().toString(36).substr(2, 9);
    }

    /**
     * Get operators for a condition type
     */
    function getOperators(conditionId) {
        const condition = conditions[conditionId];
        if (!condition) return {};

        // Use condition-specific operators if defined
        if (condition.operators) {
            return condition.operators;
        }

        // Otherwise, get by type (matching PHP Operators::for_type())
        const type = condition.type || 'text';
        const multiple = condition.multiple || false;

        switch (type) {
            case 'number':
            case 'number_unit':
                return operators.number || {};

            case 'text_unit':
                return operators.text || {};

            case 'boolean':
                return operators.boolean || {};

            case 'date':
                return operators.date || {};

            case 'time':
                return operators.time || {};

            case 'ip':
                return operators.ip || {};

            case 'email':
                return operators.email || {};

            case 'tags':
                return operators.tags || {};

            case 'select':
                return multiple ? (operators.collection || {}) : (operators.equality || {});

            case 'post':
            case 'term':
            case 'user':
            case 'ajax':
                return multiple ? (operators.collection || {}) : (operators.equality || {});

            default:
                return operators.text || {};
        }
    }

    /**
     * Render value field based on condition type
     */
    function renderValueField(conditionId, groupId, ruleId, currentValue) {
        const condition = conditions[conditionId];
        if (!condition) {
            return '<input type="text" class="value-input" disabled placeholder="' + i18n.selectCondition + '">';
        }

        const name = '_conditions[' + groupId + '][rules][' + ruleId + '][value]';
        const type = condition.type || 'text';

        switch (type) {
            case 'text':
                return renderTextInput(name, condition, currentValue);

            case 'number':
                return renderNumberInput(name, condition, currentValue);

            case 'number_unit':
                return renderNumberUnitInput(name, condition, currentValue);

            case 'text_unit':
                return renderTextUnitInput(name, condition, currentValue);

            case 'select':
                return renderSelectInput(name, condition, currentValue);

            case 'tags':
                return renderTagsInput(name, condition, currentValue);

            case 'ip':
                return renderIpInput(name, condition, currentValue);

            case 'email':
                return renderEmailInput(name, condition, currentValue);

            case 'post':
            case 'term':
            case 'user':
                return renderAjaxSelect(name, condition, currentValue);

            case 'ajax':
                return renderCustomAjaxSelect(name, condition, conditionId, currentValue);

            case 'date':
                return renderDateInput(name, condition, currentValue);

            case 'time':
                return renderTimeInput(name, condition, currentValue);

            case 'boolean':
                return ''; // Boolean uses operator only

            default:
                return renderTextInput(name, condition, currentValue);
        }
    }

    /**
     * Render text input
     */
    function renderTextInput(name, condition, value) {
        const placeholder = condition.placeholder || '';
        return '<input type="text" class="value-input" name="' + name + '" value="' + escapeHtml(value || '') + '" placeholder="' + escapeHtml(placeholder) + '">';
    }

    /**
     * Render number input
     */
    function renderNumberInput(name, condition, value) {
        const placeholder = condition.placeholder || '';
        const min = condition.min !== undefined ? ' min="' + condition.min + '"' : '';
        const max = condition.max !== undefined ? ' max="' + condition.max + '"' : '';
        const step = condition.step !== undefined ? ' step="' + condition.step + '"' : ' step="any"';

        return '<input type="number" class="value-input" name="' + name + '" value="' + escapeHtml(value || '') + '"' + min + max + step + ' placeholder="' + escapeHtml(placeholder) + '">';
    }

    /**
     * Render number with unit input
     */
    function renderNumberUnitInput(name, condition, value) {
        const numValue = (value && typeof value === 'object') ? value.number : value;
        const unitValue = (value && typeof value === 'object') ? value.unit : '';
        const units = condition.units || [];
        const placeholder = condition.placeholder || '';
        const min = condition.min !== undefined ? ' min="' + condition.min + '"' : '';
        const max = condition.max !== undefined ? ' max="' + condition.max + '"' : '';
        const step = condition.step !== undefined ? ' step="' + condition.step + '"' : ' step="any"';

        let html = '<div class="number-with-unit">';
        html += '<input type="number" class="number-input" name="' + name + '[number]" value="' + escapeHtml(numValue || '') + '"' + min + max + step + ' placeholder="' + escapeHtml(placeholder) + '">';
        html += '<select class="unit-select" name="' + name + '[unit]">';

        units.forEach(function (unit) {
            const selected = unit.value === unitValue ? ' selected' : '';
            html += '<option value="' + escapeHtml(unit.value) + '"' + selected + '>' + escapeHtml(unit.label) + '</option>';
        });

        html += '</select>';
        html += '</div>';

        return html;
    }

    /**
     * Render text with unit input
     */
    function renderTextUnitInput(name, condition, value) {
        const textValue = (value && typeof value === 'object') ? value.text : (typeof value === 'string' ? value : '');
        const unitValue = (value && typeof value === 'object') ? value.unit : '';
        const units = condition.units || [];
        const placeholder = condition.placeholder || '';

        let html = '<div class="text-with-unit">';
        html += '<input type="text" class="text-input" name="' + name + '[text]" value="' + escapeHtml(textValue || '') + '" placeholder="' + escapeHtml(placeholder) + '">';
        html += '<select class="unit-select" name="' + name + '[unit]">';

        units.forEach(function (unit) {
            const selected = unit.value === unitValue ? ' selected' : '';
            html += '<option value="' + escapeHtml(unit.value) + '"' + selected + '>' + escapeHtml(unit.label) + '</option>';
        });

        html += '</select>';
        html += '</div>';

        return html;
    }

    /**
     * Render select input
     */
    function renderSelectInput(name, condition, value) {
        let options = condition.options || [];

        // Handle callable options (shouldn't happen after PHP resolves, but just in case)
        if (typeof options === 'function') {
            options = options();
        }

        // Convert object to array if needed
        if (options && !Array.isArray(options) && typeof options === 'object') {
            options = Object.entries(options).map(function ([val, label]) {
                return {value: val, label: label};
            });
        }

        // Ensure options is an array
        if (!Array.isArray(options)) {
            console.warn('renderSelectInput: options is not an array', options);
            options = [];
        }

        const multiple = condition.multiple ? ' multiple' : '';
        const nameAttr = condition.multiple ? name + '[]' : name;
        const values = Array.isArray(value) ? value : (value ? [value] : []);
        const placeholder = condition.placeholder || i18n.selectValue;

        let html = '<select class="value-select conditions-select2' + (condition.multiple ? ' multiple' : '') + '" name="' + nameAttr + '"' + multiple + ' data-placeholder="' + escapeHtml(placeholder) + '">';

        // Add empty option for single select (allows placeholder and clear)
        if (!condition.multiple) {
            html += '<option value=""></option>';
        }

        options.forEach(function (option) {
            const optValue = option.value !== undefined ? option.value : option;
            const optLabel = option.label !== undefined ? option.label : option;
            const selected = values.includes(String(optValue)) ? ' selected' : '';
            html += '<option value="' + escapeHtml(optValue) + '"' + selected + '>' + escapeHtml(optLabel) + '</option>';
        });

        html += '</select>';

        return html;
    }

    /**
     * Render tags input (user-creatable tags)
     */
    function renderTagsInput(name, condition, value) {
        const nameAttr = name + '[]';
        const placeholder = condition.placeholder || 'Type and press Enter...';
        const values = Array.isArray(value) ? value : (value ? [value] : []);

        let html = '<select class="value-select conditions-tags-select multiple" name="' + nameAttr + '" multiple data-placeholder="' + escapeHtml(placeholder) + '">';

        // Add existing values as options
        values.forEach(function (val) {
            html += '<option value="' + escapeHtml(val) + '" selected>' + escapeHtml(val) + '</option>';
        });

        html += '</select>';

        return html;
    }

    /**
     * Render IP address input (creatable tags for IPs/CIDRs)
     */
    function renderIpInput(name, condition, value) {
        const nameAttr = name + '[]';
        const placeholder = condition.placeholder || 'Enter IP or CIDR, press Enter...';
        const values = Array.isArray(value) ? value : (value ? [value] : []);

        let html = '<select class="value-select conditions-ip-select multiple" name="' + nameAttr + '" multiple data-placeholder="' + escapeHtml(placeholder) + '">';

        // Add existing values as options
        values.forEach(function (val) {
            html += '<option value="' + escapeHtml(val) + '" selected>' + escapeHtml(val) + '</option>';
        });

        html += '</select>';

        return html;
    }

    /**
     * Render email address input (creatable tags for email patterns)
     */
    function renderEmailInput(name, condition, value) {
        const nameAttr = name + '[]';
        const placeholder = condition.placeholder || 'Enter email pattern, press Enter...';
        const values = Array.isArray(value) ? value : (value ? [value] : []);

        let html = '<select class="value-select conditions-email-select multiple" name="' + nameAttr + '" multiple data-placeholder="' + escapeHtml(placeholder) + '">';

        // Add existing values as options
        values.forEach(function (val) {
            html += '<option value="' + escapeHtml(val) + '" selected>' + escapeHtml(val) + '</option>';
        });

        html += '</select>';

        return html;
    }

    /**
     * Render AJAX-powered select (for posts, terms, users)
     */
    function renderAjaxSelect(name, condition, value) {
        const multiple = condition.multiple ? ' multiple' : '';
        const nameAttr = condition.multiple ? name + '[]' : name;
        const type = condition.type;
        const placeholder = condition.placeholder || i18n.selectValue;

        // Build data attributes for AJAX
        let dataAttrs = ' data-type="' + type + '"';
        dataAttrs += ' data-placeholder="' + escapeHtml(placeholder) + '"';

        if (type === 'post' && condition.post_type) {
            dataAttrs += ' data-post-type="' + escapeHtml(condition.post_type) + '"';
        }
        if (type === 'term' && condition.taxonomy) {
            dataAttrs += ' data-taxonomy="' + escapeHtml(condition.taxonomy) + '"';
        }
        if (type === 'user' && condition.role) {
            const roles = Array.isArray(condition.role) ? condition.role.join(',') : condition.role;
            dataAttrs += ' data-role="' + escapeHtml(roles) + '"';
        }

        let html = '<select class="value-select conditions-ajax-select' + (condition.multiple ? ' multiple' : '') + '" name="' + nameAttr + '"' + multiple + dataAttrs + '>';

        // Pre-populate with existing values (will be hydrated via AJAX)
        const values = Array.isArray(value) ? value : (value ? [value] : []);
        values.forEach(function (val) {
            html += '<option value="' + escapeHtml(val) + '" selected>' + escapeHtml(val) + '</option>';
        });

        html += '</select>';

        return html;
    }

    /**
     * Render custom AJAX select (for type => 'ajax' conditions)
     */
    function renderCustomAjaxSelect(name, condition, conditionId, value) {
        const multiple = condition.multiple ? ' multiple' : '';
        const nameAttr = condition.multiple ? name + '[]' : name;
        const placeholder = condition.placeholder || i18n.selectValue;
        const setId = $('.conditions-builder').data('set-id');

        // Build data attributes
        let dataAttrs = ' data-type="ajax"';
        dataAttrs += ' data-set-id="' + escapeHtml(setId) + '"';
        dataAttrs += ' data-condition-id="' + escapeHtml(conditionId) + '"';
        dataAttrs += ' data-placeholder="' + escapeHtml(placeholder) + '"';

        let html = '<select class="value-select conditions-custom-ajax-select' + (condition.multiple ? ' multiple' : '') + '" name="' + nameAttr + '"' + multiple + dataAttrs + '>';

        // Pre-populate with existing values (will be hydrated via AJAX)
        const values = Array.isArray(value) ? value : (value ? [value] : []);
        values.forEach(function (val) {
            html += '<option value="' + escapeHtml(val) + '" selected>' + escapeHtml(val) + '</option>';
        });

        html += '</select>';

        return html;
    }

    /**
     * Render date input
     */
    function renderDateInput(name, condition, value) {
        const placeholder = condition.placeholder || '';
        return '<input type="date" class="value-input" name="' + name + '" value="' + escapeHtml(value || '') + '" placeholder="' + escapeHtml(placeholder) + '">';
    }

    /**
     * Render time input
     */
    function renderTimeInput(name, condition, value) {
        const placeholder = condition.placeholder || '';
        return '<input type="time" class="value-input" name="' + name + '" value="' + escapeHtml(value || '') + '" placeholder="' + escapeHtml(placeholder) + '">';
    }

    /**
     * Initialize Select2 for a select element
     */
    function initSelect2($select) {
        if ($select.hasClass('select2-hidden-accessible')) {
            return; // Already initialized
        }

        const isAjax = $select.hasClass('conditions-ajax-select');
        const isCustomAjax = $select.hasClass('conditions-custom-ajax-select');
        const isTags = $select.hasClass('conditions-tags-select');
        const isIp = $select.hasClass('conditions-ip-select');
        const customPlaceholder = $select.data('placeholder');

        const options = {
            width: '100%',
            allowClear: true,
            placeholder: customPlaceholder || i18n.selectValue
        };

        // Tags mode - allow user to create new entries
        if (isTags) {
            options.tags = true;
            options.tokenSeparators = [',', ' ']; // Comma or space creates new tag
            options.createTag = function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            };
            $select.select2(options);
            return;
        }

        // IP address mode - allow user to create IP/CIDR entries
        if (isIp) {
            options.tags = true;
            options.tokenSeparators = [',', ' ']; // Comma or space creates new entry
            options.createTag = function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                // Basic validation - must look like an IP or CIDR
                // Matches: 192.168.1.1, 192.168.1.0/24, 10.*, 2001:db8::1, etc.
                if (!/^[\d.\:\/*a-fA-F]+$/.test(term)) {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            };
            $select.select2(options);
            return;
        }

        // Email pattern mode - allow user to create email pattern entries
        const isEmail = $select.hasClass('conditions-email-select');
        if (isEmail) {
            options.tags = true;
            options.tokenSeparators = [',']; // Only comma creates new entry (space is valid in some contexts)
            options.createTag = function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                // Basic validation - must look like an email pattern
                // Matches: user@domain.com, @domain.com, .edu, domain.com
                if (!/^[@\.]?[\w\.\-@]+$/.test(term)) {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            };
            $select.select2(options);
            return;
        }

        if (isAjax) {
            const type = $select.data('type');
            let endpoint = restUrl + '/' + type + 's'; // posts, terms, users

            options.ajax = {
                url: endpoint,
                dataType: 'json',
                delay: 250,
                headers: {
                    'X-WP-Nonce': nonce
                },
                data: function (params) {
                    const query = {
                        search: params.term
                    };

                    // Add type-specific params
                    if (type === 'post') {
                        query.post_type = $select.data('post-type');
                    } else if (type === 'term') {
                        query.taxonomy = $select.data('taxonomy');
                    } else if (type === 'user') {
                        query.role = $select.data('role');
                    }

                    return query;
                },
                processResults: function (data) {
                    return {
                        results: data.map(function (item) {
                            return {
                                id: item.value,
                                text: item.label
                            };
                        })
                    };
                },
                cache: true
            };

            // Get current values BEFORE initializing Select2
            const currentValues = $select.val();

            // Initialize Select2 first
            $select.select2(options);

            // Then hydrate existing values
            if (currentValues && currentValues.length) {
                const ids = Array.isArray(currentValues) ? currentValues : [currentValues];

                $.ajax({
                    url: endpoint,
                    data: {
                        include: ids.join(','),
                        post_type: $select.data('post-type'),
                        taxonomy: $select.data('taxonomy'),
                        role: $select.data('role')
                    },
                    headers: {'X-WP-Nonce': nonce}
                }).done(function (results) {
                    // Clear and re-add options with proper labels
                    $select.empty();
                    results.forEach(function (item) {
                        const option = new Option(item.label, item.value, true, true);
                        $select.append(option);
                    });
                    $select.trigger('change.select2');
                });
            }
        } else if (isCustomAjax) {
            // Custom AJAX type - uses /ajax endpoint with set_id and condition_id
            const setId = $select.data('set-id');
            const conditionId = $select.data('condition-id');
            const endpoint = restUrl + '/ajax';

            options.ajax = {
                url: endpoint,
                dataType: 'json',
                delay: 250,
                headers: {
                    'X-WP-Nonce': nonce
                },
                data: function (params) {
                    return {
                        set_id: setId,
                        condition_id: conditionId,
                        search: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function (item) {
                            return {
                                id: item.value,
                                text: item.label
                            };
                        })
                    };
                },
                cache: true
            };

            // Get current values BEFORE initializing Select2
            const currentValues = $select.val();

            // Initialize Select2 first
            $select.select2(options);

            // Then hydrate existing values
            if (currentValues && currentValues.length) {
                const ids = Array.isArray(currentValues) ? currentValues : [currentValues];

                $.ajax({
                    url: endpoint,
                    data: {
                        set_id: setId,
                        condition_id: conditionId,
                        include: ids.join(',')
                    },
                    headers: {'X-WP-Nonce': nonce}
                }).done(function (results) {
                    // Clear and re-add options with proper labels
                    $select.empty();
                    results.forEach(function (item) {
                        const option = new Option(item.label, item.value, true, true);
                        $select.append(option);
                    });
                    $select.trigger('change.select2');
                });
            }
        } else {
            // Non-AJAX select
            $select.select2(options);
        }

        // Disable browser autocomplete on Select2 search fields
        $select.on('select2:open', function () {
            const $search = $('.select2-container--open .select2-search__field');
            $search.attr('autocomplete', 'off')
                .attr('autocorrect', 'off')
                .attr('autocapitalize', 'off')
                .attr('spellcheck', 'false');
        });
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }

    /**
     * Add a condition group
     */
    function addGroup(savedData) {
        const $container = $('.condition-groups');
        const groupId = savedData?.id || generateId();
        const index = $container.find('.condition-group').length;

        // Add OR connector if not first group
        if (index > 0) {
            $container.append(wp.template('group-connector')({}));
        }

        // Render group from template
        const groupHtml = wp.template('condition-group')({
            id: groupId,
            index: index
        });

        const $group = $(groupHtml);
        $container.append($group);

        // Add conditions
        const rules = savedData?.rules || [];
        if (rules.length === 0) {
            addCondition($group, groupId);
        } else {
            rules.forEach(function (rule) {
                addCondition($group, groupId, rule);
            });
        }

        // Update remove button states
        updateRemoveButtons($group);

        return $group;
    }

    /**
     * Add a condition row
     */
    function addCondition($group, groupId, savedData) {
        const $list = $group.find('.conditions-list');
        const ruleId = savedData?.id || generateId();

        // Render condition from template
        const conditionHtml = wp.template('condition-row')({
            id: ruleId,
            groupId: groupId
        });

        const $row = $(conditionHtml);
        $list.append($row);

        // Set saved values
        if (savedData?.condition) {
            const $conditionSelect = $row.find('.condition-select');
            $conditionSelect.val(savedData.condition).trigger('change', [savedData]);
        }

        // Update remove button states
        updateRemoveButtons($group);

        return $row;
    }

    /**
     * Update remove button disabled state
     * Disable remove button if only one condition in group
     */
    function updateRemoveButtons($group) {
        const $rows = $group.find('.condition-row');
        const $removeButtons = $group.find('.remove-condition');

        if ($rows.length <= 1) {
            $removeButtons.addClass('disabled').prop('disabled', true);
        } else {
            $removeButtons.removeClass('disabled').prop('disabled', false);
        }
    }

    /**
     * Update operators when condition changes
     */
    function updateOperators($row, conditionId, savedOperator) {
        const $operatorSelect = $row.find('.operator-select');
        const ops = getOperators(conditionId);
        const opsArray = Object.entries(ops);

        console.log('updateOperators:', conditionId, 'ops:', ops, 'savedOperator:', savedOperator);

        $operatorSelect.empty();

        // Add operators
        opsArray.forEach(function ([value, label]) {
            $operatorSelect.append('<option value="' + escapeHtml(value) + '">' + escapeHtml(label) + '</option>');
        });

        $operatorSelect.prop('disabled', !conditionId || opsArray.length === 0);

        // Set saved operator or default to first
        if (savedOperator && ops[savedOperator]) {
            $operatorSelect.val(savedOperator);
        } else if (opsArray.length > 0) {
            // Default to first operator
            $operatorSelect.val(opsArray[0][0]);
        }

        console.log('Operator set to:', $operatorSelect.val());
    }

    /**
     * Update value field when condition changes
     */
    function updateValueField($row, conditionId, groupId, ruleId, savedValue) {
        const $wrapper = $row.find('.value-field-wrapper');

        // Destroy existing Select2
        $wrapper.find('.select2-hidden-accessible').select2('destroy');

        // Render new field
        const html = renderValueField(conditionId, groupId, ruleId, savedValue);
        $wrapper.html(html);

        // Initialize Select2 if needed
        $wrapper.find('.conditions-select2, .conditions-ajax-select, .conditions-custom-ajax-select, .conditions-tags-select, .conditions-ip-select, .conditions-email-select').each(function () {
            initSelect2($(this));
        });
    }

    /**
     * Update tooltip based on condition description
     */
    function updateTooltip($row, condition) {
        const $tooltip = $row.find('.condition-tooltip');

        if (condition && condition.description) {
            $tooltip.attr('data-tip', condition.description).show();
        } else {
            $tooltip.removeAttr('data-tip').hide();
        }
    }

    /**
     * Initialize the builder
     */
    function init() {
        const $builder = $('.conditions-builder');
        if (!$builder.length) return;

        console.log('Conditions builder init starting');

        // Register event handlers FIRST (before loading saved data)

        // Event: Add group
        $builder.on('click', '.add-group', function (e) {
            e.preventDefault();
            addGroup();
        });

        // Event: Add condition (button in group footer)
        $builder.on('click', '.conditions-list-footer .add-condition', function (e) {
            e.preventDefault();
            const $group = $(this).closest('.condition-group');
            const groupId = $group.data('group-id');
            addCondition($group, groupId);
        });

        // Event: Delete group
        $builder.on('click', '.delete-group', function (e) {
            e.preventDefault();
            const $group = $(this).closest('.condition-group');
            const $container = $group.parent();

            // Remove preceding OR connector
            $group.prev('.group-connector').remove();
            // Or following if first group
            $group.next('.group-connector').remove();

            $group.remove();

            // Ensure at least one group exists
            if ($container.find('.condition-group').length === 0) {
                addGroup();
            }

            // Update first group label
            $container.find('.condition-group').first().find('.group-label').text(
                i18n.matchAll || 'Match all of the following rules'
            );
        });

        // Event: Duplicate group
        $builder.on('click', '.duplicate-group', function (e) {
            e.preventDefault();
            const $group = $(this).closest('.condition-group');

            // Gather current data
            const rules = [];
            $group.find('.condition-row').each(function () {
                const $row = $(this);
                rules.push({
                    condition: $row.find('.condition-select').val(),
                    operator: $row.find('.operator-select').val(),
                    value: getRowValue($row)
                });
            });

            addGroup({rules: rules});
        });

        // Event: Remove condition
        $builder.on('click', '.remove-condition', function (e) {
            e.preventDefault();
            const $button = $(this);
            if ($button.hasClass('disabled') || $button.prop('disabled')) {
                return;
            }

            const $row = $(this).closest('.condition-row');
            const $group = $row.closest('.condition-group');

            $row.remove();

            // Update remove button states
            updateRemoveButtons($group);
        });

        // Event: Condition changed
        $builder.on('change', '.condition-select', function (e, savedData) {
            const $row = $(this).closest('.condition-row');
            const $group = $row.closest('.condition-group');
            const conditionId = $(this).val();
            const groupId = $group.data('group-id');
            const ruleId = $row.data('condition-id');
            const condition = conditions[conditionId];

            console.log('Condition changed:', conditionId);
            console.log('Saved data:', savedData);
            console.log('Condition config:', condition);

            updateOperators($row, conditionId, savedData?.operator);
            updateValueField($row, conditionId, groupId, ruleId, savedData?.value);
            updateTooltip($row, condition);
        });

        // NOW load saved conditions
        const $container = $builder.find('.condition-groups');
        const savedData = $container.data('conditions');

        console.log('Loading saved data:', savedData);

        if (savedData && savedData.length) {
            savedData.forEach(function (group) {
                addGroup(group);
            });
        } else {
            addGroup();
        }

        console.log('Conditions builder init complete');
    }

    /**
     * Get the value from a condition row
     */
    function getRowValue($row) {
        const $wrapper = $row.find('.value-field-wrapper');

        // Number with unit
        const $numberUnit = $wrapper.find('.number-with-unit');
        if ($numberUnit.length) {
            return {
                number: $numberUnit.find('.number-input').val(),
                unit: $numberUnit.find('.unit-select').val()
            };
        }

        // Text with unit
        const $textUnit = $wrapper.find('.text-with-unit');
        if ($textUnit.length) {
            return {
                text: $textUnit.find('.text-input').val(),
                unit: $textUnit.find('.unit-select').val()
            };
        }

        // Multi-select
        const $multiSelect = $wrapper.find('select[multiple]');
        if ($multiSelect.length) {
            return $multiSelect.val() || [];
        }

        // Single select
        const $select = $wrapper.find('select');
        if ($select.length) {
            return $select.val();
        }

        // Input
        const $input = $wrapper.find('input');
        if ($input.length) {
            return $input.val();
        }

        return null;
    }

    // Initialize on document ready
    $(document).ready(init);

})(jQuery);