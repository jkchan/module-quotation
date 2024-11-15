define([
    'underscore',
    'Magento_Ui/js/grid/columns/column',
    'mage/translate'
], function (_, Column, $t) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Sales_Quote/ui/grid/cells/status'
        },

        getStatusColor: function (row) {
            return row.is_valid ? 'quote-valid' : 'quote-invalid';
        },

        getLabel: function (row) {
            return row.is_valid ? $t('Valid') : $t('Not valid');
        }
    });
})
