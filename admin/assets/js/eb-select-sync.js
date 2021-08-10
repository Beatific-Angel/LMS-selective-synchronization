(function( $ ) {
    'use strict';

        $( window ).load(function() {
            /**
             * Show moodle courses and associated categories using datatable
             */
            var rows_selected = [];
            var table = $('#moodle_courses_table').DataTable({

                    "oLanguage": {
                        "sZeroRecords": "No courses found."
                    },
                    "columnDefs": [
                                    { "targets": 0, "orderable": false },
                                    { "width": "30%", "targets": 1 }
                                  ],
                    "columns": [
                                { "searchable": false },
                                null,
                                null
                             ],
                    'order': [1, 'asc'],
                    'rowCallback': function(row, data, dataIndex){
                            // Get row ID
                            var rowId = data[0];

                            // If row ID is in the list of selected row IDs
                            if($.inArray(rowId, rows_selected) !== -1){
                                $(row).find('input[type="checkbox"]').prop('checked', true);
                            }
                        }
                    });

            $('#moodle_courses_table').dataTable().columnFilter(
            {
                sPlaceHolder: "head:before",
                aoColumns: [
                                null,
                                null,
                                {
                                    type: "select",
                                    values: admin_js_select_data.category_list
                                }
                           ]
            });

            // Handle click on checkbox
            $('#moodle_courses_table tbody').on('click', 'input[type="checkbox"]', function(e){
              var $row = $(this).closest('tr');

              // Get row data
              var data = table.row($row).data();

              // Get row ID
              var rowId = data[0];

              // Determine whether row ID is in the list of selected row IDs
              var index = $.inArray(rowId, rows_selected);

              // If checkbox is checked and row ID is not in list of selected row IDs
              if(this.checked && index === -1){
                 rows_selected.push(rowId);

              // Otherwise, if checkbox is not checked and row ID is in list of selected row IDs
              } else if (!this.checked && index !== -1){
                 rows_selected.splice(index, 1);
              }

              // Update state of "Select all" control
              updateDataTableSelectAllCtrl(table);

              // Prevent click event from propagating to parent
              e.stopPropagation();
            });

            // Handle click on table cells with checkboxes
            $('#moodle_courses_table').on('click', 'tbody td, thead th:first-child, tfoot th:first-child', function(e){
                $(this).parent().find('input[type="checkbox"]').trigger('click');
            });

           // Handle click on "Select all" control
            $('#moodle_courses_table input[name="select_all_course"]').on('click', function(e){
                  if(this.checked){
                     $('#moodle_courses_table tfoot input[type="checkbox"]:not(:checked)').trigger('click');
                     $('#moodle_courses_table thead input[type="checkbox"]:not(:checked)').trigger('click');
                     $('#moodle_courses_table tbody input[type="checkbox"]:not(:checked)').trigger('click');
                  } else {
                     $('#moodle_courses_table tfoot input[type="checkbox"]:checked').trigger('click');
                     $('#moodle_courses_table thead input[type="checkbox"]:checked').trigger('click');
                     $('#moodle_courses_table tbody input[type="checkbox"]:checked').trigger('click');
                  }

                  // Prevent click event from propagating to parent
                  e.stopPropagation();
            });



            $('#moodle_courses_table input[name="chksel_course"]').on('click', function(e){
                if(this.checked){
                    var count = 0;
                        
                    $('#moodle_courses_table input[name="chksel_course"]').each(function(){
                        if($(this).prop('checked')){
                            count ++;
                        }

                    });

                    if ($('#moodle_courses_table input[name="chksel_course"]').length == count) {

                        // $('#moodle_courses_table input[name="select_all_course"]').prop("checked", true);
                        
                        $('.select_all_course_cb').prop("checked", true);

                        // $('#moodle_courses_table input[name="select_all_course"]').each(function(){
                        //     $(this).attr("checked", true);
                        // });
                    }

                } else {
                    $('.select_all_course_cb').prop("checked", false);
                    //$('#moodle_courses_table input[name="select_all_course"]').prop("checked", false);
                }
            });



            // Handle table draw event
            table.on('draw', function(){
                // Update state of "Select all" control
                updateDataTableSelectAllCtrl(table);
            });

            $( ".eb-filter select" ).change(function() {

                var stable = $('#moodle_courses_table').dataTable();

                $("input:checked", stable.fnGetNodes()).each(function(){
                    $(this).prop( "checked", false );
                });

                // Update state of "Select all" control
                updateDataTableSelectAllCtrl(table);
            });


            $('#eb_sync_selected_course_button').click(function(){

                $('.response-box').empty();

                //display loading animation
                $('.load-response').show();

                var stable = $('#moodle_courses_table').dataTable();

                var sids = new Array();

                $("input:checked", stable.fnGetNodes()).each(function(){
                    sids.push($(this).val());
                });

                var update_course = $('#eb_update_selected_courses').is(':checked')? 1: 0;

                if( sids.length <= 0 )
                {
                    $('.load-response').hide();
                    ohSnap( admin_js_select_data.chk_error , 'error', 0);
                }
                else
                {
                    $.ajax({
                        method      : "post",
                        url         : admin_js_select_data.admin_ajax_path,
                        dataType    : "json",
                        data: {
                            'action'           : 'selective_course_sync',
                            '_wpnonce_field'   : admin_js_select_data.nonce,
                            'selected_courses' : sids,
                            'update_course'    : update_course,
                        },
                        success:function(response) {
                            $('.load-response').hide();

                            //prepare response for user
                            if( response.connection_response == 1 ){
                                if( response.course_success == 1 )
                                {
                                    ohSnap(admin_js_select_data.select_success, 'success', 1);
                                }
                                else
                                    ohSnap(response.course_response_message, 'error', 0);

                            } else {
                                ohSnap(admin_js_select_data.connect_error, 'error', 0);
                            }
                        }
                    });
                }
            });


            /******************************************
             * user creation js.
             ******************************************/

            $(document).on('click', '.eb-ss-msg-dismiss', function(){
                $(this).parent().css('display', 'none');
            });

            $(document).on('click', '.eb_ss_user_bulk_actions', function(event){
                event.preventDefault();

                //necessary starter things like loader , removing old error msgs.
                $('#eb-lading-parent').css('display', 'block');
                $('.eb-ss-users-error-wrap').css('display', 'none');

                var users = new Array();
                $(".eb-ss-user-tbl-cb:checked").each(function(){
                    users.push({
                        'id':$(this).val(),
                        'first_name' : $(this).data('fname'),
                        'last_name' : $(this).data('lname'),
                        'username' : $(this).data('username'),
                        'email' : $(this).data('email'),
                    });
                });

                var bulk_action = $(this).parent();
                bulk_action = bulk_action.find('.eb-ss-user-bulk-action-dropdown');

                bulk_action = bulk_action.val();

                $.ajax({
                    method      : "post",
                    url         : admin_js_select_data.admin_ajax_path,
                    dataType    : "json",
                    data: {
                        'action'           : 'selective_users_sync',
                        '_wpnonce_field'   : admin_js_select_data.nonce,
                        'bulk_action'      : bulk_action,
                        'users'            : users,
                    },
                    success:function(response) {
                        $('#eb-lading-parent').css('display', 'none');
                        if (response.data) {
                            $('.eb-ss-users-error-wrap').css('display', 'block');
                            $('.eb-ss-users-error-wrap').html(response.data);
                        }
                    },
                    error: function(response){
                        $('#eb-lading-parent').css('display', 'none');
                        alert(admin_js_select_data.ajax_error);
                    }
                });
            });


            $('#eb-ss-all-users-submit').click(function(event){
                event.preventDefault();

                $('.response-box').empty(); // empty the response
                $('.linkresponse-box').empty();

                $('body').css('cursor', 'no-drop');

                var create_users = $('#selective_synch_create_all_users').prop('checked') ? 1:0;
                var link_users   = $('#selective_synch_link_all_users').prop('checked') ? 1:0;

                $('.response-box').html('<div class="eb-ss-user-warning-msgs"><span class="dashicons dashicons-warning"></span> ' + admin_js_select_data.all_user_synch_warning + '</div>');

                $('.load-response').show();
                eb_ss_synch_all_users(create_users, link_users);

            });



            /**
             * ------------------IMPORTANT --------------------
             * added extraa varible as the query result from the moodle has not removed the guest user after removing the guest user from moodle result itself then remove below variables.
             * display_offset
             * --------------------------------------------------
            */
            function eb_ss_synch_all_users(create_users, link_users, display_offset = 0, offset = 0, total_users = 0)
            {
                $.ajax({
                    method      : "post",
                    url         : admin_js_select_data.admin_ajax_path,
                    dataType    : "json",
                    data: {
                        'action'           : 'all_users_sync',
                        '_wpnonce_field'   : admin_js_select_data.nonce,
                        'create_users'     : create_users,
                        'display_offset'   : display_offset,
                        'link_users'       : link_users,
                        'offset'           : offset,
                        // 'count'            : count,
                    },
                    success:function(response) {
                        if (response.data) {
                            var html       = '';
                            offset         = offset + response.data.processed_users;

                            /**
                             * added below line as the query result from the moodle has not removed the gurst user after removing the guest user from moodle result itself then remove this line
                             */
                            display_offset = display_offset + response.data.display_offset;

                            total_users    = response.data.total_users;
                            var msg        = response.data.msg;


                            if (response.success) {

                                // offset is less than the total no. off users the start the loop.
                                if (offset < total_users) {
                                    msg = '<div class="eb-ss-user-warning-msgs"><span class="dashicons dashicons-warning"></span> ' + admin_js_select_data.all_user_synch_warning + '</div>' + msg;
                                    //get count of no. of users processed and add them to the offset from the response and add it to the old count
                                    eb_ss_synch_all_users(create_users, link_users, display_offset, offset, total_users);
                                } else {
                                    //show the link to the popup if any of the users linking or creation is failed.
                                    if (response.data.error) {
                                        $('.eb-ss-users-migration-error-tbl tbody').html(response.data.error_response);
                                    }

                                    //loader
                                    $('body').css('cursor', 'default');
                                    $('.load-response').hide();
                                }
                                //exit from the loop if offset equals to the total users.

                            } else {
                                $('body').css('cursor', 'default');
                                $('.load-response').hide();
                            }

                            $('.response-box').html(msg);


                        }
                    },
                    error: function(response){
                        alert(admin_js_select_data.ajax_error);
                    }
                });
            }





            $(document).on('click', '.eb-ss-migration-error-show-pop-up', function(){

                $('.eb-ss-users-migration-error-tbl').DataTable({
                    "autoWidth": false,
                    dom: 'Bfrtip',
                    /*buttons: [
                        'csv'
                    ],*/
                    buttons: [
                        {
                            extend: 'csv',
                            text: 'Export as CSV'
                        }
                    ],
                    "columns": [
                        { "width": "50%" },
                        { "width": "50%" },
                    ],

                });

                $('.eb-ss-users-migration-error-tbl-wrap').dialog({
                    width: "60%",
                    dialogClass: "eb-ss-users-error-dialog",
                    open: function() {
                        $('.eb-ss-dialog-overlay').css('display', 'block');

                        $('.ui-widget-overlay').addClass('eb-ss-users-error-tbl-overlay');
                    },
                     close: function (event) {
                        $('.eb-ss-dialog-overlay').css('display', 'none');
                    },
                });
            });


            /************  Users js END  ***********/
    });

    function ohSnap(text, type, status) {
          // text : message to show (HTML tag allowed)
          // Available colors : red, green, blue, orange, yellow --- add your own!

          // Set some variables
          var time = '10000';
          var container = jQuery('.response-box');

          // Generate the HTML
          var html = '<div class="alert alert-' + type + '">' + text + '</div>';

          // Append the label to the container
          container.append(html);
    }

    // Updates "Select all" control in a data table
    function updateDataTableSelectAllCtrl(table){
        var $table                  = table.table().node();
        var $chkbox_all             = $('tbody input[type="checkbox"]', $table);
        var $chkbox_checked         = $('tbody input[type="checkbox"]:checked', $table);
        var chkbox_select_all       = $('thead input[name="select_all_course"]', $table).get(0);
        var chkbox_select_all_foot  = $('tfoot input[name="select_all_course"]', $table).get(0);

       // If none of the checkboxes are checked
       if($chkbox_checked.length === 0){
          chkbox_select_all.checked = false;
          chkbox_select_all_foot.checked = false;
          if('indeterminate' in chkbox_select_all){
             chkbox_select_all.indeterminate = false;
             chkbox_select_all_foot.indeterminate = false;
          }

       // If all of the checkboxes are checked
       } else if ($chkbox_checked.length === $chkbox_all.length){
          chkbox_select_all.checked = true;
          chkbox_select_all_foot.checked = true;
          if('indeterminate' in chkbox_select_all){
             chkbox_select_all.indeterminate = false;
             chkbox_select_all_foot.indeterminate = false;
          }

       // If some of the checkboxes are checked
       } else {
          chkbox_select_all.checked = true;
          chkbox_select_all_foot.checked = true;
          if('indeterminate' in chkbox_select_all){
             chkbox_select_all.indeterminate = true;
             chkbox_select_all_foot.indeterminate = true;
          }
       }
    }

})( jQuery );