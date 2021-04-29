var rpicloud = {
    render:function ($){
        var basehash = '#!folder';

        $(".tree").fancytree({
            renderNode: function (event,data){
                var node = data.node;
                if( node.data.mimetype ){
                    // Open target
                    data.node.li.className = node.data.mimetype;

                    //console.log(data.node.li);

                }
            },
            expand: function(event, data) {
                //aconsole.log(event, data);
            },
            create: function(event) {


                // look for hash in url e.g.  #!folder_4
                // and open node on start

                setTimeout(function (){

                    if( location.hash.indexOf("!folder_") > 0 ){
                        let tree = $.ui.fancytree.getTree('#'+event.target.id);
                        if(tree){
                            let node = tree.activateKey(location.hash.substring(8));
                            if (node)
                                node.toggleExpanded();
                        }
                    }



                },1000);



            },
            keydown:function (event,data){

                if(event.originalEvent.originalEvent.key == "Enter"){
                    var node = data.node;
                    if( node.data.href){
                        // Open target
                        location.href = node.data.href;

                    }else{
                        node.toggleExpanded();

                    }
                }

            },
            renderNode: function (event,data){
                var node = data.node;

                if( node.data.href ){
                    // Use <a> href and target attributes to load the content:
                    node.setTitle('<a href="' + node.data.href + '">'+node.title+'</a>');

                }
            },
            dblclick: function(event, data) {
                var node = data.node;
                // download/display on dbl click
                if( node.data.href ){
                    // Open target
                    location.href = node.data.href;
                }

            },

            activate: function(event, data) {
                var node = data.node;
                if( node.data.href ){
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
    if(!window.___dedect__wpLoadBlockEditor){
        rpicloud.render($);
    }
});

const ___dedect__wpLoadBlockEditor_id = setInterval(function()
{
    if(!window.___dedect__wpLoadBlockEditor)
        window.___dedect__wpLoadBlockEditor =0;

    if ( window._wpLoadBlockEditor ) {
        window._wpLoadBlockEditor.then( function() {
            console.log( 'hooray!' );
            clearInterval(___dedect__wpLoadBlockEditor_id);
            setTimeout(function (){
                //rpicloud.render(jQuery);

                $(".tree").fancytree();


            },1000)

        } )
    }

    window.___dedect__wpLoadBlockEditor ++

    if(window.___dedect__wpLoadBlockEditor > 10 ){
        clearInterval(___dedect__wpLoadBlockEditor_id);
    }

    console.log('detecting...');

}, 1000);
