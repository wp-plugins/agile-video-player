// http://www.garyc40.com/2010/03/how-to-make-shortcodes-user-friendly/
// closure to avoid namespace collision
(function(){
	// creates the plugin
    tinymce.create('tinymce.plugins.videoinsertbuttons', {
		init : function(ed, url) {
            tinymce.plugins.videoinsertbuttons.theurl = url;
			
		// Register commands
  //           ed.addButton('videoinsertbuttons', {
  //               title : 'Insert Agile Video Player Shortcode',
		// 	type : 'button',
  //               // text: 'Insert Video',
  //              image: tinymce.plugins.videoinsertbuttons.theurl + '/app_logo.png',

		// 	// Uncomment this only simple shortcode needed
		// 	/*
		// 	onclick : function() {
		// 		ed.selection.setContent('[jazzyoptin]');
		// 	}*/

		// 	// Pull dialog page for buttons with options
		// 	onclick: function() {
		// 		ed.windowManager.open({
  //                   file : tinymce.plugins.videoinsertbuttons.theurl + '/spp-ddl-shortcode-dialog.php', // file that contains HTML for our modal window
		// 			width : 400 + parseInt(ed.getLang('button.delta_width', 0)), // size of our window
		// 			height : 120 + parseInt(ed.getLang('button.delta_height', 0)), // size of our window
		// 			inline : 1
		// 		}, {
  //                   plugin_url : tinymce.plugins.videoinsertbuttons.theurl
		// 		});
		// 	}
		// });
		},
		
		getInfo : function() {
			return {
                longname : 'Agile Video Player',
                author : 'Team',
                authorurl : 'http://agilevideoplayer.com',
                infourl : 'http://agilevideoplayer.com',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
		
	});
	
	// registers the plugin. DON'T MISS THIS STEP!!!
    tinymce.PluginManager.add('videoinsertbuttons', tinymce.plugins.videoinsertbuttons);
	
})();