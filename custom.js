jQuery(document).ready( function($) {

        var orderpage = "false";
        orderpage = $("#ordermanagementpage").val();
        var productpage = "false";
        productpage = $("#productmanagementpage").val();
        var logmodule = "false";
        logmodule = $("#logmodule").val();

        if(orderpage == "true") {
            $('#example').DataTable({
                "processing": true,
                "serverSide": true,
                ajax :{
                    url :'admin-ajax.php',

                    data : {
                        action   : 'order_datatable'
                    },
                    dataSrc: function ( json ) {
                        return json.data;
                    }
                },
                "columnDefs": [ {
                    "targets": 4,
                    "orderable": false,
                    "data": null,
                    "render" : function(data, type, row) {
                        if(data[4] == 0){
                            return '<a href="javascript:void(0)" class="btn btn-primary SingleOrderSync" id="'+data[4]+'" data-id="'+data[1]+'">Sync order</a>&nbsp&nbsp<img style="display:none;" src="'+data[5]+'" id="orderloader_'+data[1]+'">';
                        }else{
                            return '<span class = "label label-success" style="font-size:12px;">Already Synced</span>';
                        }
                    }
                },
                    {
                        "targets": 0,
                        "orderable": false,
                        "data": null,
                        "render" : function(data, type, row) {
                            return '<a href="#" id="'+data[1]+'" data-id="'+data[1]+'" class="viewOrderDetails"><i class="fa fa-eye"></i></a>&nbsp;&nbsp&nbsp;&nbsp<a href="'+data[6]+'" target="_blank" id="'+data[1]+'" data-id="'+data[1]+'"><i class="fa fa-external-link"></i></a>';
                        }
                    },
                    {
                        "targets": 3,
                        "data": null,
                        "render" : function(data, type, row) {
                            if(data[3] == 'on-hold'){
                                return '<span class = "label label-warning" style="font-size:12px;">On Hold</span>';
                            }else if(data[3] == 'cancelled'){
                                return '<span class = "label label-danger" style="font-size:12px;">Cancelled</span>';
                            }else if(data[3] == 'processing'){
                                return '<span class = "label label-primary" style="font-size:12px;">Processing</span>';
                            }else if(data[3] == 'pending'){
                                return '<span class = "label label-warning" style="font-size:12px;">Pending</span>';
                            }else if(data[3] == 'completed'){
                                return '<span class = "label label-success" style="font-size:12px;">Completed</span>';
                            }else if(data[3] == 'refunded'){
                                return '<span class = "label label-info" style="font-size:12px;">Refunded</span>';
                            }else if(data[3] == 'failed'){
                                return '<span class = "label label-danger" style="font-size:12px;">Failed</span>';
                            }else if(data[3] == 'failed'){
                                return '<span class = "label label-danger" style="font-size:12px;">Failed</span>';
                            }else if(data[3] == 'trash'){
                                return '<span class = "label label-warning" style="font-size:12px;">Trash</span>';
                            }
                        }
                    }
                ]
            });
        }

        if(productpage == "true"){
            $('.updateproductdatatable').DataTable();
        }

        if(logmodule == "true"){
           $('.logsofmodules').DataTable({"order": [[ 0, "desc" ]]});
        }


    $(".ywcars_button_refund").html("ASK FOR A RETURN");
    $(".woocommerce-MyAccount-navigation-link--refund-requests > a").html('MY RETURN REQUESTS');
    
    var myvalue = 'false';
    myvalue = $("#orderspage").val();
    if(myvalue == 'true'){
        $(document).ready(function() {
            $('.ywcars_button_refund').html('ASK FOR A RETURN');
        });
    }

    $("#syncitemgroup").click( function() {
        $(".button").attr('disabled','disabled');
        $("#itemgrouploader").css('display','');
        var data = {
            action: 'sw_sync_item_groups',
            post_var: 'this will be echoed back'
        };
        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $(".button").removeAttr('disabled');
            $("#itemgrouploader").css('display','none');
            $("#modalcontent").text('Categories sync done !');
            $("#notificationmodal").modal("show");
            // alert('Itemgroups sync done !');
        });
        return false;
    });



    $("#syncitem").click( function() {
        var itemcount = $(".counts").val();
        $(".button").attr('disabled','disabled');
        $("#itemloader").css('display','');
        $("#itemsby").attr("disabled",'disabled');

        var data = {
            action: 'test_response',
            post_var: 'this will be echoed back',
            item_count : itemcount,
        };
        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $(".button").removeAttr('disabled');
            $("#itemloader").css('display','none');
            $("#modalcontent").text('products sync done !');
            $("#notificationmodal").modal("show");
            $("#itemsby").removeAttr('disabled');
            //alert('products sync done !');
        });
        return false;
    });



    $("#syncorders").click(function(){
        $("#syncorders").attr('disabled','disabled');
        $("#orderloader").css('display','');
        var data = {
            action: 'my_action',
            post_var: 'this will be echoed back'
        };
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $("#syncorders").removeAttr('disabled');
            $("#orderloader").css('display','none');
            $("#modalcontent").text('Orders sync done !');
            $("#notificationmodal").modal("show");
            // alert("Orders sync done !");
        });
        return false;
    });

    $("#createvariations").click(function(){
        $(".button").attr('disabled','disabled');
        $("#variationloader").css('display','');

        var data = {
            action: 'sw_createvariations',
            post_var: 'this will be echoed back'
        };
        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $(".button").removeAttr('disabled');
            $("#variationloader").css('display','none');
            $("#modalcontent").text('Product variations sync done !');
            $("#notificationmodal").modal("show");
            // alert('Product Variation created');
        });

    });

    $("#updatevariations").click(function(){
        var itemcount = $("#updatevariationcount").val();
        $(".button").attr('disabled','disabled');
        $("#variationupdateloader").css('display','');

        var data = {
            action: 'sw_updatevariations',
            post_var: 'this will be echoed back',
            itemcount: itemcount,
        };
        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $(".button").removeAttr('disabled');
            $("#variationupdateloader").css('display','none');
            $("#modalcontent").text('Product variations are updated!');
            $("#notificationmodal").modal("show");
            // alert('Product Variation created');
        });

    });

    $("#updatestocks").click(function(){
        $(".button").attr('disabled','disabled');
        var itemcount = $(".stockcount").val();
        $("#updatestocksloader").css('display','');

        var data = {
            action: 'sw_updatestocks',
            post_var: 'this will be echoed back',
            item_count: itemcount
        };
        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $(".button").removeAttr('disabled');
            $("#updatestocksloader").css('display','none');
            $("#modalcontent").text('Product stocks are updated!');
            $("#notificationmodal").modal("show");
            // alert('Product Variation created');
        });

    });


    //for the order tab sync single order with erp orders.

    jQuery(document).on('click','.SingleOrderSync',function(){
        $(".SingleOrderSync").attr('disabled','disabled');
        var order_id = $(this).attr('data-id');
        $("#orderloader_"+order_id).css('display','');
        // return false;
        var erp_id = $(this).attr('id');
        var data = {
            action: 'erp_order_sync',
            post_var: {'orderId':order_id,'erpId':erp_id}
        };
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $(".SingleOrderSync").removeAttr('disabled');
            $("#orderloader_"+order_id).css('display','none');
            $("#modalcontent").text('Order sync done !');
            $("#notificationmodal").modal("show");
            //window.location.reload();
        });
        return false;
    });


    jQuery(document).on('click','.viewOrderDetails',function(){
        var order_id = $(this).attr('data-id');
        var data = {
            action: 'order_details',
            post_var: 'this will be echoed back',
            orderId : order_id
        };
        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $("#modalcontentorders").html(response);
            $("#orderdetailmodal").modal("show");
            // alert('Itemgroups sync done !');
        });
        return false;
    });

    //for the order tab Update all orders status with erp orders.
    $("#UpdateOrdersStatus").click(function(){
        $("#UpdateOrdersStatus").attr('disabled','disabled');
        $("#UpdateOrdersLoader").css('display','');
        $("#orderlists").css('opacity', '0.3');
        var data = {
            action: 'sync_orders_status',
            post_var: 'this will be echoed back'
        };
        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            // $("#createvariations").removeAttr('disabled');
            $("#orderlists").css('opacity', 'unset');
            $("#UpdateOrdersLoader").css('display','none');
            $.alert('Orders Update done !');
            $("#UpdateOrdersStatus").removeAttr('disabled');
        });
        return false;
    });

    $('#woocommSubmit').on('click', function(e) {
        var wc_key = $('#woocommerceKeys').val();
        var wc_sec_key = $('#woocommerceSecretkeys').val();
        var url = $('#apiUrl').val();
        var token = $('#token').val();
        if(wc_key && wc_sec_key && url && token){
            var data = {
                action: 'wc_keys_save',
                post_var: 'this will be echoed back',
                wc_key: wc_key,
                wc_secret_key:wc_sec_key,
                api_url: url,
                token:token
            };
            // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
            $.post(the_ajax_script.ajaxurl, data, function(response) {
                $.alert('WooCommerce Keys Change done !');
            });
        }else{
            $.alert('Please enter valid input !');
        }
        return false;
    });


    $('#clearProducts').on('click', function(e) {
        $.confirm({
            title: 'Confirm!',
            content: 'Are you sure , you want to reset all Products and Categories ?',
            buttons: {
                confirm: function () {

                    $("#clearProductloader").css('display','');
                    var data = {
                        action: 'reset_product_category',
                        post_var: 'this will be echoed back'
                    };
                    // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
                    $.post(the_ajax_script.ajaxurl, data, function(response) {
                        $("#clearProductloader").css('display','none');
                        $.alert('reset completed !');
                        window.location.reload();
                    });
                },
                cancel: function () {

                },
            }
        });
    });


    //updates product one by one
    $(document).on("click",".updateproduct",function(){
        $(this).attr('disabled','disabled');
        var id  = $(this).attr('id');
        $("#loader_"+id).css('display','');

        var data = {
            action: 'sw_updateproduct',
            product_id: id,
        };
        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $("#"+id).removeAttr('disabled');
            $("#loader_"+id).css('display','none');
            $("#modalcontent").text('Product is updated!');
            $("#notificationmodal").modal("show");
            // alert('Product Variation created');
        });
    });

    //updates product one by one but "bycolors"
    $(document).on("click",".updateproductbycolor",function(){
        $(this).attr('disabled','disabled');
        var wpid = $(this).attr('id');
        $("#loaderbycolor_"+wpid).css('display','');

        var data = {
            action : 'sw_updateproduct',
            product_id : wpid,
        };

        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $("#"+wpid).removeAttr('disabled');
            $("#loaderbycolor_"+wpid).css('display','none');
            $("#modalcontent").text('Product is updated!');
            $("#notificationmodal").modal("show");
            // alert('Product Variation created');
        });
    });



    //calls upload_plugin function in ajax
    $("#uploadplugin").on("click",function(){
        $(this).attr('disabled','disabled');
        $("#uploadpluginloader").css('display','');

        var data = {
            action: 'upload_plugin',
        };

        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $("#uploadplugin").removeAttr('disabled');
            $("#uploadpluginloader").css('display','none');
            $("#modalcontent").text('Plugin is uploaded to https://www.blackandgoldofficial.com/');
            $("#notificationmodal").modal("show");
            // alert('Product Variation created');
        });

    });


    //ajax call for submitting preference for creating items
    $("#itemsby").on("change",function(){
        $("#syncitem").attr("disabled","disabled");
        $("#itemsbyloader").css('display','');
        var preference = $("#itemsby").val();

        var data = {
            action : 'set_preference',
            pref : preference
        };

        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $("#syncitem").removeAttr('disabled');
            $("#itemsbyloader").css('display','none');
            $("#modalcontent").text('Your preference is set !');
            $("#notificationmodal").modal("show");
            // alert('Product Variation created');
        });
    });


    $("#updatetrackinginfo").click(function(){
        $(".button").attr('disabled','disabled');
        $("#updatetrackingloader").css('display','');

        var data = {
            action: 'sw_updatetrackingstatus',
            post_var: 'this will be echoed back'
        };
        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $(".button").removeAttr('disabled');
            $("#updatetrackingloader").css('display','none');
            $("#modalcontent").text('Tracking info for orders is updated!');
            $("#notificationmodal").modal("show");
            // alert('Product Variation created');
        });

    });


    $("#update-category").click( function() {
        $(".button").attr('disabled','disabled');
        $("#update-category-loader").css('display','');
        $("#update-category").attr("disabled",'disabled');
        console.info('asjdhjhajh');
        var data = {
            action: 'sw_category_updates',
            post_var: 'this will be echoed back',
        };
        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $(".button").removeAttr('disabled');
            $("#update-category-loader").css('display','none');
            $("#modalcontent").text('Categories Update done !');
            $("#notificationmodal").modal("show");
            $("#update-category").removeAttr('disabled');
            //alert('products sync done !');
        });
        return false;
    });


    jQuery(window).load(function(){
        var check = 'false';
        jQuery("#pa_color option.enabled").each(function(){
            jQuery(this).attr('selected','selected');
            check = 'true';
            return false;
        });

        jQuery("#pa_size option.enabled").each(function(){
            jQuery(this).attr('selected','selected');
            check = 'true';
            return false;
        });

        if(check == 'true'){
            jQuery('.single_add_to_cart_button').removeClass('disabled wc-variation-selection-needed');
        }
    });

    $("#multicurrency").click(function(){
       $(".button").attr('disabled','disabled');
       $("#multicurrencyloader").css('display','');

       var data = {
           action: 'multi_currency',
           post_var: 'this will be echoed back'
       };

        $.post(the_ajax_script.ajaxurl, data, function(response) {
            $(".button").removeAttr('disabled');
            $("#multicurrencyloader").css('display','none');
            $("#modalcontent").text('Multicurrency is updated.');
            $("#notificationmodal").modal("show");
        });

    });



    /* use this ajax call order error remove display from db change status of notifications */
    jQuery(document).on("click",".notice-dismiss",function(){
        var order_id = jQuery(this).parents().attr('data-id');
        console.info(order_id);
        var data = {
            action: 'order_error_notification_close',
            post_var: 'this will be echoed back',
            order_id : order_id,
        };
        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, data, function(response) {
        
        });
        return false;
    });
   
});