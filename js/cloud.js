var rpicloud = {

    check_name: function (){
        let $ = jQuery;
        arr = $('.rpicloud-user');
        if(arr.length>1){
            for(i=1; i < arr.length;i++){
                arr[i].remove();
            }
        }

        console.log('user:'+$('#rpicloud-user').val());

        if(username = $('#rpicloud-user').val()){
            $('.cloud-username').val(username);
            $('.toolbar-username').html(username).css({'float':'left'});
            $('.rpicloud-user').slideUp();
            return true;
        }else{
            $('.rpicloud-user').slideDown();
            $('#rpicloud-user').focus();
        }
        return false;

    },
    checkupload: function(id){
        if(!jQuery('#upload_'+id+'_container :file').val()){
            jQuery('#cloud-upload-field-'+id).css('border','2px dashed red');
            console.log('#cloud-upload-field-'+id);
            return false;
        }
        console.log(true);
        return true;
    },
    checkcreatedir: function(id){
        if(!jQuery('#cloud-createdir-'+id).val()){
            jQuery('#cloud-createdir-'+id).css('border','2px dashed red');

            console.log('#cloud-createdir-'+id);
            return false;
        }else{
            let v = jQuery('#cloud-createdir-'+id).val();
            v = v.replace('/','');
            jQuery('#cloud-createdir-'+id).val(v);
            console.log(true);
            return true;
        }

    },

    showupload_dialog: function(id){
        $ = jQuery;
        if(!rpicloud.check_name()){
            return;
        }
        let tb = '#upload_'+id+'_container';
        console.log(tb);
        $(tb).slideToggle(function (){
            let hdl = $(this)
                .parent('.rpicloud-container')
                .find('.rpicloud-handle.upload span');

            if($(this).css('display') =='none'){
                hdl.css({'background-color':'#fff','color':'#999'});
            }else{
                hdl.css({'background-color':'green','color':'#fff'});

            }
        });



    },
    showcreatedir_dialog: function(id){
        $ = jQuery;
        if(!rpicloud.check_name()){
            return;
        }
        let tb = '#createdir_'+id+'_container';
        console.log(tb);
        $(tb).slideToggle(function (){
            let hdl = $(this)
                .parent('.rpicloud-container')
                .find('.rpicloud-handle.createdir span');

            if($(this).css('display') =='none'){
                hdl.css({'background-color':'#fff','color':'#999'});
            }else{
                hdl.css({'background-color':'green','color':'#fff'});

            }
        });
    },
    togglelog:function (id){
        $ = jQuery;

        if(!id){
            id = 'tree1';
        }

        let tb = '#'+id+'-cloud-log.rpicloud-log';
        $(tb).slideToggle(function (){
            let hdl = $(this)
                .parent('.rpicloud-container')
                .find('.rpicloud-handle.log span');

            console.log(hdl);

            if($(this).css('display') =='none'){
                hdl.css({'background-color':'#fff','color':'#999'});
            }else{
                hdl.css({'background-color':'#777','color':'#fff'});

            }
        });




    },

    delete: function(id, msg){

        if(!rpicloud.check_name()){
            return;
        }

        $ = jQuery;

        id = id.replace('del_','');


        //console.info(id);

        let node = $.ui.fancytree.getTree('#'+id).getActiveNode();
        if(!node){
            return;
        }

        $('#'+ id +'-cloud-del-file').val(node.data.file);
        $('#'+ id +'-cloud-del-nodekey').val($('#'+ id +'-cloud-upload-nodekey').val());
        $('#'+ id +'-cloud-base-dir').val($('#'+ id +'-cloud-upload-dir').val());
        $('#'+ id +'-cloud-base-startdir').val($('#'+ id +'-cloud-upload-startdir').val());


        let filename = node.title.replace(/<[^>]*>/,'');

        let elem = $('#'+ id +'-cloud-confirm-delete-message');
        elem.first('.cloud-confirm-delete-message').find('.cloud-confirm-delete-message').html(msg.replace('%name%', '<strong>"'+filename+'"</strong>'));
        elem.slideToggle(function (){

            let tree = '#'+ id+'-container';

            let hdl = $(tree).find('.rpicloud-handle.delete span');

            console.log(hdl);

            if($(this).css('display') =='none'){
                hdl.css({'background-color':'#fff','color':'#999'});
            }else{
                hdl.css({'background-color':'red','color':'#fff'});

            }
        });


    },

    toolbar: function(elem){

        let $ = jQuery;

        elem = $('#'+elem);


        let tb = elem.find('.rpicloud-toolbar');
        if(tb){
            console.log(tb);

            let btn = elem.find('.rpicloud-handle');

            if( btn.length>0 ){
                tb.css('display', 'flex');
            }


            tb.append(elem.find('.rpicloud-handle'));
        }


    },

    render:function ($){

        var basehash = '#!folder';

        $('.rpicloud-user').slideUp();
        $('.rpicloud-container').each(function (e,elem){

            rpicloud.toolbar(elem.id);
        })

        $(".tree").fancytree({


            renderNode: function (event,data){
                var node = data.node;
                if( node.data.mimetype ){
                    // Open target
                    data.node.li.className = node.data.mimetype;

                    //console.log(data.node.li);

                }
                if( node.data.href ){
                    // Use <a> href and target attributes to load the content:
                    node.data.display = node.data.href;
                    node.setTitle('<a href="' + node.data.href.replace('cloudview', 'cloud') + '">'+node.title+'</a>');


                }
            },

            create: function(event,data) {

                rpicloud.togglelog(event.target.id);

                iFrameResize();
                // look for hash in url e.g.  #!folder_4
                // and open node on start

                setTimeout(function (){

                    if( location.hash.indexOf("!folder_") >= 0 ){
                        let tree = $.ui.fancytree.getTree('#'+event.target.id);
                        if(tree){
                            let node = tree.activateKey(location.hash.substring(8));
                            if (node && !node.expanded){
                                node.toggleExpanded();
                            }
                            $(".tree a").click(function (e){e.preventDefault();});

                        }
                    }



                },1000);

                $(window).on('hashchange', function() {

                    if( location.hash.indexOf("!folder_") > 0 ){
                        let tree = $.ui.fancytree.getTree('#'+event.target.id);
                        if(tree){
                            let node = tree.activateKey(location.hash.substring(8));
                            if (node && !node.expanded){
                                node.toggleExpanded();
                            }
                        }
                    }
                });


            },
            keydown:function (event,data){

                if(event.originalEvent.originalEvent.key == "Enter"){
                    var node = data.node;
                    if( node.data.href){
                        // Open target
                        location.href = node.data.display ;

                    }else{
                        node.toggleExpanded();

                    }
                }

            },

            click: function(event, data) {
                var node = data.node;
                // download/display on dbl click
                if( data.node.isActive() ){
                    // Open target
                    if( node.data.href ){
                        if(event.ctrlKey){
                            window.open(node.data.display);
                        }else if(event.shiftKey){
                            window.open(node.data.display,'_blank');
                        }else{
                            location.href = node.data.display ;
                        }
                    }
                    return false;
                }

            },

            expand:function(event, data) {
                var node = data.node;
                console.log(basehash + node.key);
                location.hash=basehash + node.key;
                $('#' + event.target.id + '-cloud-upload-nodekey').val(basehash + node.key);
                $('#' + event.target.id + '-cloud-delete-nodekey').val(basehash + node.key);
                iFrameResize();

            },
            activate: function(event, data) {
                var node = data.node;
                if( node.data.href ){

                    //console.dir(node.parent.key);
                    $('#'+ event.target.id +'-cloud-upload-nodekey').val(basehash + node.parent.key);

                    // Open target
                    // Use <a> href and target attributes to load the content:
                    //location.href = node.data.href;
                    //console.log(node.title);
                }
                else{
                    //console.log(node);
                    //change Form Value
                    $('#' + event.target.id + '-cloud-upload-nodekey').val(basehash + node.key);

                    location.hash=basehash.substring(1) + node.key;

                    let dir = "/";
                    while (node.parent && node.parent.expanded == true){

                        dir = "/"+ node.title + dir;
                        node = node.parent?node.parent:null;

                    }
                    //console.log(dir);
                    //change Form Value
                    $('#' + event.target.id + '-cloud-upload-dir').val(dir);

                }

            }
        });

    }
}


jQuery(document).ready(function($){
    setTimeout(function (){
        rpicloud.render($);
    },1);
});
