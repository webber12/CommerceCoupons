<!DOCTYPE HTML>
<html>
    <head>
    <link rel="stylesheet" href="media/style/common/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="[+module_url+]/vendor/7.1.3/webix.min.css" type="text/css">
    <link rel="stylesheet" href="[+module_url+]skin/skin.css" type="text/css">
    <style>
        body.webix_full_screen{overflow:auto !important;}
        /*.webix_view.webix_pager{margin-bottom:30px;}
        .webix_cell{-webkit-transition: all .3s,-moz-transition: all .3s,-o-transition: all .3s,transition: all .3s}
        .webix_cell:nth-child(odd){background-color:#f6f8f8;}
        .webix_cell:hover{background-color: rgba(93, 109, 202, 0.16);}*/
    </style>
    <script src="[+module_url+]/vendor/7.1.3/webix.min.js" type="text/javascript"></script>
    <script src="[+module_url+]/vendor/7.1.3/i18n/ru.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript" src="https://cdn.webix.com/components/edge/ckeditor5/ckeditor5.js"></script>
    </head>
    <body class="[+manager_theme_mode+]">
        <div id="wbx_table" style="padding-bottom:20px;"></div>
        <div id="wbx_pp" style="padding-bottom:20px;width:90%;"></div>
    
        <script type="text/javascript" charset="utf-8">

        webix.ready(function() {
            webix.i18n.setLocale("ru-RU");
            webix.attachEvent("onBeforeAjax", 
                function(mode, url, data, request, headers, files, promise){
                    headers["X-Requested-With"] = "XMLHttpRequest";
                }
            );
            webix.editors.$popup = {
                date:{
                    view:"popup",
                    body:{ 
                        view:"calendar", 
                        /*timepicker:true, 
                        timepickerHeight:50,*/
                        width: 320, 
                        height:300,
                        calendarDateFormat: "%Y-%m-%d"
                    }
                },
                text:{
                    view:"popup", 
                    body:{view:"textarea", width:350, height:150}
                }
            };
            var form = {
                view:"form",
                id:"myform",
                borderless:true,
                elements: [
                    [+formfields+] ,
                    { margin:5, cols:[
                        { view:"button", value: "Submit", type:"form", css:"webix_primary", click:submit_form},
                        { view:"button", value: "Cancel", click: function (elementId, event) {
                            this.getTopParentView().hide();
                        }}
                    ]},
                    {rows : [
                        {template:"The End", type:"section"}
                    ]}
                ],
                rules:{},
                elementsConfig:{
                    labelPosition:"top",
                },
                height:500,
                scroll:"y"
            };

            var search_form = {
                view:"form",
                id:"searchform",
                elements: [
                    { margin:5, cols:
                        [+search_formfields+]
                    },
                ]
            };
            webix.ui({
                view:"window",
                id:"win2",
                width:500,
                height:500,
                position:"center",
                modal:true,
                head:{
                    view:"toolbar", margin:5, cols:[
                        {view:"label", label: "Редактирование данных" },
                        {view:"icon", icon:"wxi-check", click:submit_form, tooltip:"Сохранить изменения"},
                        {view:"icon", icon:"wxi-close", click:"$$('win2').hide();", tooltip:"Закрыть без сохранения"}
                        ]
                },
                body:form
            });
            var form_generate = {
                view:"form",
                id:"form_generate",
                borderless:true,
                elements: [
                    {view:"counter", label:"Количество купонов", name:"count", value:"5", min:"1"},
                    {view:"text", label:"Имя купона", name:"name"},
                    {view:"counter", label:"Количество символов", name:"length", value:"8", min:"3", max:"15"},
                    {view:"datepicker", label:"Действителен с", name:"date_start"},
                    {view:"datepicker", label:"Действителен по", name:"date_finish"},
                    {view:"text", label:"Скидка %", name:"discount", pattern:{ mask:"##", allow:/[0-9]/g}, value:"5", min:"1"},
                    {view:"text", label:"Скидка руб (если не заполнена скидка в %)", name:"discount_summ", pattern:{ mask:"####", allow:/[0-9]/g}, min:"1"},
                    {view:"counter", label:"Лимит заказов по купону", name:"limit_orders", value:"1"},
                    {view:"checkbox", label:"Сразу активен", name:"active", value:"1"},
                    { margin:5, cols:[
                        { view:"button", value: "Submit", type:"form", css:"webix_primary", click:generate_coupons},
                        { view:"button", value: "Cancel", click: function (elementId, event) {
                            this.getTopParentView().hide();
                        }}
                    ]},
                    {rows : [
                        {template:"The End", type:"section"}
                    ]}
                ],
                rules:{},
                elementsConfig:{
                    labelPosition:"top",
                },
                height:500,
                scroll:"y"
            };
            webix.ui({
                view:"window",
                id:"win_generate",
                width:500,
                height:500,
                position:"center",
                modal:true,
                head:{
                    view:"toolbar", margin:5, cols:[
                        {view:"label", label: "Массовое создание купонов" },
                        {view:"icon", icon:"wxi-check", click:generate_coupons, tooltip:"Сохранить изменения"},
                        {view:"icon", icon:"wxi-close", click:"$$('win_generate').hide();", tooltip:"Закрыть без сохранения"}
                        ]
                },
                body:form_generate
            });
            webix.ui({
                container:"wbx_table",
                rows:[
                    { view:"template", type:"header", template:"[+name+]"},
                    { view:"toolbar", id:"mybar", elements:[
                        { view:"button", type:"icon", icon:"wxi-folder-open", label:"Автогенерация", width:150, css:"webix_primary", click:"make_coupons"},
                        { view:"button", type:"icon", icon:"wxi-plus-circle", label:"Добавить", width:120, css:"webix_primary", click:"add_row"}, 
                        [+modal_edit_btn+]
                        { view:"button", type:"icon", icon:"wxi-sync", label:"", tooltip:"Обновить данные", click:"refresh", autowidth:true  },
                        { view:"button", type:"icon", icon:"wxi-radiobox-blank", label:"", tooltip:"Перегрузить полностью", click:"reload", autowidth:true },
                        { view:"button", type:"icon", icon:"wxi-trash",  label:"Удалить", width:110, css:"webix_danger", click:"del_row" },
                        ]
                    },
                    [+add_search_form+]
                    { view:"datatable",
                        autoheight:true,select:"row",
                        resizeColumn:true,
                        id:"mydatatable",
                        editable:[+inline_edit+],
                        editaction: "dblclick",
                        datafetch:[+display+],
                        navigation:true,
                        columns : [+cols+] ,
                        pager:{   
                            size : [+display+],
                            group : 5,
                            template : "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                            container:"wbx_pp"
                        },
                        url: "[+module_url+]action.php?action=List&module_id=[+module_id+]",
                        save: "[+module_url+]action.php?action=Update&module_id=[+module_id+]",
                        delete: "[+module_url+]action.php?action=Delete&module_id=[+module_id+]"
                    }
                ]
            });
            webix.ui({
                view:"contextmenu",
                id:"cmenu",
                data:[[+context_edit_btn+]"Удалить"],
                on:{
                    onItemClick:function(id){
                        var action = this.getItem(id).value;
                        switch (action) {
                            case 'Удалить':
                                del_row();
                                break;
                            case 'Правка':
                                edit_row();
                                break;
                            default:break;
                        }
                    }
                }
            });
            $$("cmenu").attachTo($$("mydatatable"));
        });

        function add_row() {
            webix.ajax('[+module_url+]action.php?action=GetNext&module_id=[+module_id+]').then(function(data){
                var data = data.json();
                if (typeof data.max != "undefined") {
                    var ins = {'[+idField+]' : data.max};
                    $$("mydatatable").add(ins, 0).refresh();
                }
            });
        }
        function del_row() {
            var selected = $$("mydatatable").getSelectedId();
            if (typeof(selected) !== "undefined") {
                webix.confirm("Вы уверены, что хотите удалить выбранную строку?", "confirm-warning", function(result){
                    if (result === true) {
                        $$("mydatatable").remove(selected);
                    }
                });
            } else {
                show_alert("Вы не выбрали строку для удаления", "alert-warning");
            }
        }
        function resetTable() {
            $$("mydatatable").eachColumn( function(pCol) { var f = this.getFilter(pCol); if (f) if (f.value) f.value = ""; });
            $$("mydatatable").clearAll();
            $$("mydatatable").setState({});
            $$("mydatatable").load($$("mydatatable").config.url);
        }
        function refresh(str = '') {
            $$("mydatatable").clearAll();
            $$("mydatatable").load($$("mydatatable").config.url + str, "json", refreshState);
        }
        function refreshState() {
            var mydatatable_state = webix.storage.local.get("mydatatable_state");
            if (mydatatable_state) {$$("mydatatable").setState(mydatatable_state);}
        }
        function edit_row(){
            var selected = $$("mydatatable").getSelectedId();
            if (typeof(selected) !== "undefined") {
                $$("win2").getBody().clear();
                $$("win2").show();
                $$("myform").load("[+module_url+]action.php?action=GetRow&module_id=[+module_id+]&key=" + selected);
            } else {
                show_alert("Вы не выбрали строку для редактирования", "alert-warning");
            }
        }
        function submit_form() {
            var mydatatable_state = $$("mydatatable").getState();
            webix.storage.local.put("mydatatable_state", mydatatable_state);
            webix.ajax().post("[+module_url+]action.php?action=UpdateRow&module_id=[+module_id+]", $$("myform").getValues(), function(text, data, xhr){ 
                if (text == 'ok') {
                    var selected = $$("mydatatable").getSelectedId();
                    webix.ajax("[+module_url+]action.php?action=GetRow&module_id=[+module_id+]&key=" + selected).then(function(data){
                        var data = data.json();
                        var item = $$("mydatatable").getItem(selected);
                        for (k in item) {
                            if (k != 'id') {
                                item[k] = data[k];
                            }
                        }
                        $$("mydatatable").refresh();
                    });
                    //refresh();
                    show_alert('Изменения успешно сохранены', "alert-success");
                } else {
                    show_alert('Ошибка на сервере, попробуйте позднее', "alert-warning");
                }
            });
        }
        function add_search() {
            var obj = $$("searchform").getValues();
            var str = '';
            for (key in obj) {
                str = str + '&' + key + '=' + obj[key];
            }
            refresh(str);
        }
        function show_alert(text, level) {
            webix.alert(text, level, function(result){});
        }
        function reload() {
            document.location.reload(true);
        }
        function make_coupons() {
            /*$$("win_generate").getBody().clear();*/
            $$("win_generate").show();
        }
        function generate_coupons() {
            webix.ajax().post("[+module_url+]action.php?action=GenerateCoupons&module_id=[+module_id+]", $$("form_generate").getValues(), function(text, data, xhr){ 
                if (text == 'ok') {
                    show_alert('Новые купоны успешно сгенерированы', "alert-success");
                    refresh();
                } else {
                    show_alert('Ошибка на сервере, попробуйте позднее', "alert-warning");
                }
            });
        }
        </script>
    </body>
</html>