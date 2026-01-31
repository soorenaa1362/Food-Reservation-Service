/*=========================================================================================
    File Name: datatables-styling.js
    Description: Styling Datatable
    ----------------------------------------------------------------------------------------
    Item Name: Convex - Bootstrap 4 HTML Admin Dashboard Template
    Version: 1.0
    Author: PIXINVENT
    Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/
(function(window, document, $) {
    'use strict';
	$(document).ready(function() {

	/********************************
	*       js of Base style        *
	********************************/

	$('.base-style').DataTable({
		language: {
			url: '/js/lang/fa.json'
		}
	});

	/******************************
	*       js of no style        *
	******************************/

	$('.no-style-no').DataTable({
		language: {
			url: '/js/lang/fa.json'
		}
	});

	$('.compact').DataTable({
		language: {
			url: '/js/lang/fa.json'
		}
	});

	$('.bootstrap-3').DataTable({
		language: {
			url: '/js/lang/fa.json'
		}
	});

        /****************************************************************
         *   ✅ Select All logic for `.center-table` (all pages)
         ****************************************************************/
        if ($('.center-table').length) {
            // Use existing DataTable instance — no re-init
            var centerTable = $('.center-table').DataTable();
			window.selectedCenters = new Set();

            function syncPageCheckboxes() {
                centerTable.rows({ page: 'current' }).nodes().to$().find('.center-checkbox').each(function () {
                    var id = String($(this).data('center-id'));
                    this.checked = selectedCenters.has(id);
                });
            }

            function updateSelectAllButton() {
                var checkboxes = centerTable.rows({ search: 'applied' }).nodes().to$().find('.center-checkbox');
                var total = checkboxes.length;
                var checked = checkboxes.filter(':checked').length;
                $('#selectAll').text(total > 0 && checked === total ? 'لغو انتخاب همه' : 'انتخاب همه');
            }

            // Single checkbox change
            $('.center-table').on('change', '.center-checkbox', function () {
                var id = String($(this).data('center-id'));
                if (this.checked) {
                    selectedCenters.add(id);
                } else {
                    selectedCenters.delete(id);
                }
                updateSelectAllButton();
            });

            // Select/Deselect all across all filtered rows
            $('#selectAll').on('click', function () {
                var checkboxes = centerTable.rows({ search: 'applied' }).nodes().to$().find('.center-checkbox');
                var isAllSelected = checkboxes.length > 0 && checkboxes.filter(':checked').length === checkboxes.length;

                checkboxes.prop('checked', !isAllSelected).each(function () {
                    var id = String($(this).data('center-id'));
                    if (!isAllSelected) {
                        selectedCenters.add(id);
                    } else {
                        selectedCenters.delete(id);
                    }
                });

                $(this).text(isAllSelected ? 'انتخاب همه' : 'لغو انتخاب همه');
            });

            // Keep sync on pagination / search / sort
            centerTable.on('draw', function () {
                syncPageCheckboxes();
                updateSelectAllButton();
            });

            // Hidden input before form submit
            $('#inlineForm form').on('submit', function () {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'selected_centers',
                    value: Array.from(selectedCenters).join(',')
                }).appendTo(this);
            });

            // Initial sync
            syncPageCheckboxes();
            updateSelectAllButton();
        }

	} );
})(window, document, jQuery);