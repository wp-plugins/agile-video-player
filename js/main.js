//FOR YT PLAYER EXECUTION 
function execute_yt_player(){
   var d = false; 
    var t;
    
jQuery("#ytplayer").tubeplayer({
	width: 600, // the width of the player
	height: 450, // the height of the player
	allowFullScreen: "true", // true by default, allow user to go full screen
	initialVideo: "e5BBzcWJDjU", // the video that is loaded into the player
    start: 0, 
	preferredQuality: "default",// preferred quality: default, small, medium, large, hd720
    showControls: 1, // whether the player should have the controls visible, 0 or 1
	showRelated: 0, // show the related videos when the player ends, 0 or 1 
	autoPlay: false, // whether the player should autoplay the video, 0 or 1
	autoHide: true, 
	theme: "dark", // possible options: "dark" or "light"
	color: "red", // possible options: "red" or "white"
	showinfo: false, // if you want the player to include details about the video
	modestbranding: true, // specify to include/exclude the YouTube watermark
	// the location to the swfobject import for the flash player, default to Google's CDN
	wmode: "transparent", // note: transparent maintains z-index, but disables GPU acceleration
	swfobjectURL: "http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js",
	loadSWFObject: true, // if you include swfobject, set to false
	// HTML5 specific attrs
	iframed: true, // iframed can be: true, false; if true, but not supported, degrades to flash
	// Player Trigger Specific Functionality
	onPlay: function(id){
    
        
        
    }, // after the play method is called
	onPause: function(){
    
    }, // after the pause method is called
	onStop: function(){}, // after the player is stopped
	onSeek: function(time){}, // after the video has been seeked to a defined point
	onMute: function(){}, // after the player is muted
	onUnMute: function(){}, // after the player is unmuted
	// Player State Change Specific Functionality
	onPlayerUnstarted: function(){

    }, // when the player returns a state of unstarted
	onPlayerEnded: function(){
        
       jQuery( ".text_ex" ).show();
        
    }, // when the player returns a state of ended
	onPlayerPlaying: function(){
    
    t = setInterval(handleint,100);
    jQuery( ".text_ex" ).hide();    
    
    }, //when the player returns a state of playing
	onPlayerPaused: function(){
    
      jQuery( ".text_ex" ).show();
    
    }, // when the player returns a state of paused
	onPlayerBuffering: function(){}, // when the player returns a state of buffering
	onPlayerCued: function(){}, // when the player returns a state of cued
	onQualityChange: function(quality){}, // a function callback for when the quality of a video is determined
	// Error State Specific Functionality
	onErrorNotFound: function(){}, // if a video cant be found
	onErrorNotEmbeddable: function(){}, // if a video isnt embeddable
	onErrorInvalidParameter: function(){}, // if we've got an invalid param

      });
     
    function handleint() {
        if (jQuery("#ytplayer").tubeplayer("data").currentTime >= 4 && jQuery("#ytplayer").tubeplayer("data").currentTime <= 5 && !d) {
              jQuery("#ytplayer").tubeplayer("stop");
             jQuery('.y-form').show();
            jQuery('.close_cta_bt').show();
            jQuery('.cta_image').show();
            jQuery('.cta_text').show();
             jQuery('.bottom-box').show();  
             d = true;
        }
    }    
}
//FOR YT PLAYER EXECUTION END

//FOR ACTIONS EXECUTION
function execute_actions(){
    
jQuery("#em").validate({
                    expression: "if (VAL.match(/^[^\\W][a-zA-Z0-9\\_\\-\\.]+([a-zA-Z0-9\\_\\-\\.]+)*\\@[a-zA-Z0-9_]+(\\.[a-zA-Z0-9_]+)*\\.[a-zA-Z]{2,4}$/)){ jQuery('.y-form').hide(); jQuery('#ytplayer').tubeplayer('play');jQuery('.bottom-box').hide(); jQuery.cookie('test', 't'); return true;} else{ return false; }",
                    message: "Please enter a valid email id"
                });
    
     //JQUERY FOR CALL TO ACTION
             
             //FOR TEXT
    jQuery('.open_cta_bt').click(function(){
            jQuery('.open_cta_bt').hide();
            jQuery('.close_cta_bt').show();
            jQuery('.cta_text').slideDown(1000);
             
        });
              jQuery('.close_cta_bt').click(function(){
            jQuery('.close_cta_bt').hide();
            jQuery('.open_cta_bt').show();
            jQuery('.cta_text').slideUp(1000);       
        });
             
              jQuery('.cta_text').click(function(){
                 
             });
             
             //FOR TEXT END
             
             //FOR IMAGE 
//           jQuery('.open_cta_bt').click(function(){
//            jQuery('.open_cta_bt').hide();
//            jQuery('.close_cta_bt').show();
//            jQuery('.cta_image').slideDown(1000);
//             
//        });
//              jQuery('.close_cta_bt').click(function(){
//            jQuery('.close_cta_bt').hide();
//            jQuery('.open_cta_bt').show();
//            jQuery('.cta_image').slideUp(1000);       
//        });  
//             
//             jQuery('.cta_image').click(function(){
//                 
//             });
             
             //FOR IMAGE END
             
             //JQUERY FOR CALL TO ACTION END
             
          jQuery( ".close-btn-red-20" ).click(function() {
     
        jQuery(".y-form").hide();      
        jQuery("#ytplayer").tubeplayer("play");
        jQuery('.bottom-box').hide();         
});
    
         jQuery( ".text_ex" ).click(function() {
                 jQuery(".text_ex").hide();
});
    
}
//FOR ACTIONS EXECUTION END