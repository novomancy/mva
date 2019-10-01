<?php
  /**
   * This page is protected by either Dartmouth's CAS auth system (uses Apache mod_auth_cas)
   * or basic http auth.
   * This section grabs user info from CAS/LDAP or ./.userlist to automatically register the
   * user's name and email for annotation provenance. These accounts are not registered
   * on the annotation server, they are only used for attribution.
   */

  //conf
  //$auth_type can be 'Basic' or 'CAS'
  //'Basic' is apache auth
  //'CAS' is CAS (specific to Dartmouth)
  $auth_type = 'Basic';

  //$load_type can be 'Directory' or 'File'
  //If 'Directory', it scans the current directory for .mp4 files and builds a list for the dropdown
  //If 'File', it parses video_list.csv to produce the dropdown of available films
  $load_type = 'File';

  $selected_video = isset($_GET['video']) && $_GET['video']!='' ? urldecode($_GET['video']) : false;

  $error = '';

  if($auth_type==='CAS'){
    require_once('classes/DNDUser.class.php');

    //Get data from LDAP
    try{
      //CAS returns full.name@DARTMOUTH.EDU as a server var
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
  } else if ($auth_type === "Basic") {
    require_once('classes/ApacheUser.class.php');

    try{
      //ApacheUser handles all the interaction with htpasswd and the user list
      $user = new ApacheUser();
    } catch (Exception $e){
      $error = $e;
    }
  }

  $videos = Array();

  if($load_type==='Directory'){
    /**
     * This page will load a list of videos from the directory below for the 
     * user to annotate.
     */

    $video_base_dir = '/var/www/vhosts/mediaecology/collections/shanghai';
    $video_base_url = '//mediaecology.dartmouth.edu/collections/shanghai/';

    if ($video_dir = opendir($video_base_dir)) {
      while (false !== ($fn = readdir($video_dir))) {
          if (substr($fn, -4, 4)=='.mp4') {
              $file = explode('/', $fn);
              $name = array_pop($file);
              $videos[substr($name, -4, 4)] = $video_base_url . $name; 
          }
      }
      closedir($video_dir);
    }
  } else if($load_type==='File'){
    /**
     * This page will load a list of videos from video_list.csv. It should have two columns: title, url
     */
    $file_handle = fopen('./video_list.csv', 'r');

    //remove header row
    fgetcsv($file_handle);

    while($data_row = fgetcsv($file_handle)){
      $videos[$data_row[0]] = $data_row[1];
    }

    fclose($file_handle);
  }
  ksort($videos, SORT_NATURAL);

  $logout_url = 'http://username@mediaecology.dartmouth.edu/acrh/index.php';
  // $logout_url = 'https://login.dartmouth.edu/logout.php?app=MEP&url=http://mediaecology.dartmouth.edu/shanghai_annotator/index.php';

?>
<!doctype html>
<html class="no-js" lang="">
  
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Minimum Viable Annotator</title>
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
        <div id="title">Minimum Viable Annotator</div>
        <div id="login-box" style="text-align: right">
          Annotating as <?php echo $user->get_attribute('name'); ?> (<?php echo $user->get_attribute('email'); ?>) <a href="<?php echo $logout_url; ?>">Log Out</a><br>
          Current video: <select id="current-video">
          <option value="">Select Video</option>
          <?php foreach($videos as $name => $url ){ echo "<option value='".urlencode($url)."'".($selected_video === $url ? ' selected' : '').">$name</option>\n"; } ?>
          </select>
        </div>
      </header>
      <br clear="both">
      <?php if($error != ''){ ?>
      <section>
        <p>
          Error getting user information from LDAP: <?php echo $error; ?><br>
          Please contact us at <a href="mailto:mep@groups.dartmouth.edu">mep@groups.dartmouth.edu</a>.
        </p>
      </section>      
      <?php } ?>
      <?php if( $selected_video ){ ?>
      <section>
        <div id="page-video">
          <video id="annotating-video" width="640" height="480" controls>
          <source src="<?php echo $selected_video; ?>" type="video/mp4">
          Your browser does not support the video tag.
          </video>
          <br />
        </div>
        <div id="index-container">
          <h4>Annotation Index</h4>
          <div>
            Filter by tag: <select id="tag-filter"></select><br>
            Filter by author: <select id="author-filter"></select>
          <div class="waldorf-index"></div>
        </div>
      </section>
    <?php } else { ?>
    Please select a video file to annotate.
    <?php } ?>      
    </div>
  </body>
  <script>
    var resetFilters = function(){
      //getAnnotations returns all annotations in order of beginning time
      var annos = waldorf.GetAnnotations();
      var authors = {};
      var tags = [];
      var tagSelect = $('#tag-filter');
      var authorSelect = $('#author-filter');

      tags.push("All tags");
      authors['All Authors'] = 'All Authors';

      for(var a=0; a<annos.length; a++){
        for(var t=0; t<annos[a].tags.length; t++){
          if(tags.indexOf(annos[a].tags[t]) < 0) tags.push(annos[a].tags[t]);
        }
        for(var t=0; t<annos[a].tags.length; t++){
          if(tags.indexOf(annos[a].tags[t]) < 0) tags.push(annos[a].tags[t]);
        }
        if(typeof authors[annos[a].creator.email] === 'undefined') authors[annos[a].creator.email] = annos[a].creator.nickname;
      }
      tags.sort();
      resetTagFilter(tags, tagSelect);
      resetAuthorFilter(authors, authorSelect);

      tagSelect.selectedIndex = 0;
      authorSelect.selectedIndex = 0;
    }

    function resetTagFilter(tags, tagSelect){
      tagSelect.empty();
      for(var i=0; i<tags.length; i++){
        $('<option value="'+tags[i]+'">'+tags[i]+'</option>').appendTo(tagSelect);
      }
    }

    function resetAuthorFilter(authors, authorSelect){
      authorSelect.selectedIndex = 0;
      for(a in authors){
        $('<option value="'+a+'">'+authors[a]+'</option>').appendTo(authorSelect);
      };
    }

    var setListeners = function(_waldorf){
      //Set useful global
      waldorf = _waldorf;

        waldorf.$container.on("OnAnnotationsLoaded", 
          (event) => resetFilters());
        waldorf.$container.on("OnAnnotationRegistered",
          (event) => resetFilters());
        waldorf.$container.on("OnAnnotationRemoved",
          (event) => resetFilters());         
    }

    $('body').ready(function(){
      $('#current-video').on("change", function(){
        var sel = $('#current-video').val();
        if(sel=='') return false;
        console.log(window.location.href.split('?')[0])
        var url = window.location.href.split('?')[0];
        location = url + '?video=' + sel;
      });

      if($("video").length < 1) return;
      // This is for entering annotations
      var serverAddress = "http://ec2-18-221-127-156.us-east-2.compute.amazonaws.com:3000";
      var tagsAddress = "https://onomy.org/published/83/json";
      var apiKey = "facc287b-2f51-431d-87ec-773e12302fcf";
      waldorf = $("video").first().annotate({serverURL: serverAddress, 
                                  tagsURL: tagsAddress, 
                                  apiKey: apiKey, 
                                  kioskMode: false,
                                  cmsUsername: "<?php echo $user->get_attribute('name'); ?>",
                                  cmsEmail: "<?php echo $user->get_attribute('email'); ?>",
                                  displayIndex: true,
                                  callback: setListeners});
      $('#tag-filter').on("change", function(){
        var selectedTag = $('#tag-filter').val();
        $('.waldorf-index ul li').show();

        if(selectedTag == 'All tags'){
          return;
        } 

        $('.waldorf-index ul li').filter(function(i){
          return $(this).data('tags').indexOf(selectedTag) < 0;
        }).hide();
      });

      $('#author-filter').on("change",function(){
        var selectedAuthor = $('#author-filter').val();
        $('.waldorf-index ul li').show();

        if(selectedAuthor == 'All Authors') {
          return;
        }
        
        $('.waldorf-index ul li').filter(function(i){
          return $(this).data('creator') != selectedAuthor;
        }).hide();
      });
    });
  </script>   
</html>