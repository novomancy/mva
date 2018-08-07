<?php
  ini_set('display_errors', 1);
  error_reporting(E_ALL ^ E_NOTICE);
  require_once('classes/DNDUser.class.php');

  //Get data from LDAP
  $error = '';
  try{
    $name_only = preg_split("/@/", $_SERVER['REMOTE_USER']);
		$name_only = $name_only[0];
    $user = new DNDUser($name_only);
    $user->do_lookup();
  } catch (Exception $e){
    $error = $e;
  }
  
  //Add constructed attributes and humanize LDAP names
  if($error == ''){
    $user->humanize();
  }
?>
<!doctype html>
<html class="no-js" lang="">
  
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>DartFS Storage Request</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


  <link rel="stylesheet" href="css/normalize.min.css">
  <link rel="stylesheet" href="css/main.css">
    <!--[if lt IE 9]>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script>window.html5 || document.write('<script src="js/vendor/html5shiv.js"><\/script>')</script>
    <![endif]-->
  <!-- Import JQuery -->
  <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
  <!-- Import JQueryUI -->
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <!-- This is loading out of order using grunt so loading manually here - JPB -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.1/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.1/js/select2.min.js"></script>        
  <!-- Annotator Core -->
  <script type="text/javascript" src="js/annotator-frontend.js"></script>
  <link rel="stylesheet" href="css/annotator-frontend.css">
</head>

<body>
  <!--[if lte IE 9]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
  <![endif]-->
  <div class="container">
    <header>
      <h1>Minimum Viable Annotator</h1>
    </header>
    <?php if($error != ''){ ?>
    <section>
      <p>
        Error getting user information from LDAP: <?php echo $error; ?><br>
        Please contact us at <a href="mailto:mep@groups.dartmouth.edu">mep@groups.dartmouth.edu</a>.
      </p>
    </section>      
    <?php } ?>
    <section>
    Annotating as <?php echo $user->get_attribute('name'); ?> (<?php echo $user->get_attribute('email'); ?>)<br>
    <a href="https://login.dartmouth.edu/logout.php?app=MEP&url=http://mediaecology.dartmouth.edu/shanghai_annotator/index.php">Log Out</a>
    </section>
    <section>
      <div id="page-video">
        <video id="monks-video" width="640" height="480" controls>
        <source src="https://rcweb.dartmouth.edu/MEP/shanghai_video/ChangXiangSi01.mp4" type="video/mp4">
        Your browser does not support the video tag.
        </video>
        <br />
      </div>
    </section>
  </div>
</body>
<script>
            // useSimplifiedCharacters = false;
            // waldorf = false;
            // $('#toggle-character-button').click(function(){
            //     useSimplifiedCharacters = !useSimplifiedCharacters;
            //     waldorf.UpdateViews();
            //     updateDisplay(waldorf.infoContainer.$container);
            //     return false;
            // });

            // function nextAnnotation(){
            //     var activeSet = useSimplifiedCharacters ? simplified : traditional;
            //     for(var i=0; i<activeSet.length; i++){
            //         if(activeSet[i].beginTime <= waldorf.player.videoElement.currentTime && 
            //             activeSet[i].endTime >= waldorf.player.videoElement.currentTime) return i;
            //         if(activeSet[i].beginTime >= waldorf.player.videoElement.currentTime) return i;
            //     }
            //     return activeSet.length - 1;
            // }

            // function updateDisplay(){
            //     container = waldorf.infoContainer.$container;
            //     var horizon = 2;
            //     var activeSet = useSimplifiedCharacters ? simplified : traditional;
            //     container.empty();
            //     var $panel = $("<div></div>").appendTo(container);
            //     for(i = currentAnnotation-horizon; i<=currentAnnotation+horizon; i++){
            //         if(i >= 0 && i < activeSet.length){
            //             var $content = $("<span class='waldorf-tag-"+activeSet[i].tags.join(", ")+"' style='font-size: 2em; display: block; margin-bottom: .5em'></span>");
            //             if(i == currentAnnotation && (activeSet[i].beginTime < waldorf.player.videoElement.currentTime &&
            //                activeSet[i].endTime > waldorf.player.videoElement.currentTime)){
            //                 $content.addClass('activeAnnotation');
            //             } 
            //             $content.append(activeSet[i].body.filter(item => item.purpose === "describing")[0].value);
            //             $content.append($('<br>'));
            //             //attach the annotation to the container and return the container
            //             $panel.append($content);
            //         }
            //     }
            //     return $panel;
            // }

            //This is an advanced option to create a custom renderer for annotations
            // var customRenderer = function(annotator, annotation, index){
            //     //This renderer assumes that annotations do not overlap, other than
            //     //equivalent simplified and traditional annotations
            //     //In this case we're only rendering annotations matching a certain tag

            //     //trap for no tags
            //     if(!$.isArray(annotation.tags)) return false;

            //     //only render if this annotation matches the currently displayed set
            //     if(((annotation.tags[0] == "simplified") && useSimplifiedCharacters) || 
            //          ((annotation.tags[0] == "traditional") && !useSimplifiedCharacters)){
            //         //Since the internal annotations array may be unordered, we want to
            //         //use the passed annotation ID as a key to search the simplified or 
            //         //traditional arrays (which are ordered) and render x annotations
            //         //before and after the current one, which will be highlighted.

            //         var key = annotation.id;
            //         var activeSet = useSimplifiedCharacters ? simplified : traditional;
            //         currentAnnotation = activeSet.indexOf(annotation);

            //         return updateDisplay();
            //     }
            // }

            // var customUnrenderer = function(annotator){
            //     currentAnnotation = nextAnnotation();
            //     updateDisplay();
            //     annotator.infoContainer.$container.find('span').removeClass('activeAnnotation');
            // }

            // var customCallback = function(_waldorf){
            //     //Set useful global
            //     waldorf = _waldorf;

            //     //Set globals for both types of annotations (simplified and traditional)
            //     //getAnnotations returns all annotations in order of beginning time
            //     waldorf.$container.on("OnAnnotationsLoaded", function(){
            //         var phrases = waldorf.GetAnnotations();
            //         traditional = $.grep(phrases, function(a){return a.tags[0]=="traditional"});
            //         simplified = $.grep(phrases, function(a){return a.tags[0]=="simplified"});
            //         currentAnnotation = 0;
            //     });
            // }

            $('body').ready(function(){
                // This is for entering annotations
                var serverAddress = "http://ec2-18-221-127-156.us-east-2.compute.amazonaws.com:3000";
                var tagsAddress = "https://onomy.org/published/83/json";
                var apiKey = "facc287b-2f51-431d-87ec-773e12302fcf";
                waldorf = $("video").first().annotate({serverURL: serverAddress, 
                                            tagsURL: tagsAddress, 
                                            apiKey: apiKey, 
                                            kioskMode: false,
                                            cmsUsername: "<?php echo $user->get_attribute('name'); ?>",
                                            cmsEmail: "<?php echo $user->get_attribute('email'); ?>"
                                            // renderer: customRenderer,
                                            // unrenderer: customUnrenderer,
                                            // callback: customCallback});
                });
                
                // This is for kiosk mode
                // var localAnnotations = "https://rcweb.dartmouth.edu/~f001m9b/js/annotation_cache.json";
                // var tagsAddress = "https://onomy.org/published/83/json";
                // $("video").first().annotate({localURL: localAnnotations,
                //                              renderer: customRenderer,
                //                              unrenderer: customUnrenderer,
                //                              clearContainer: false,
                //                              callback: customCallback}); //kioskMode=true is implied
            });
        </script>   
</html>