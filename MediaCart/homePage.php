<?php
// Start Session
session_start();

//Include the config.php file
require_once "config.php";

// Check to see if the username is still logged in, if not send them to login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Get Profile Image

// Prepare a select statement
$sql = "SELECT profileImage FROM users WHERE userID = ?";

if($stmt = mysqli_prepare($link, $sql)){
    // Bind variables to the prepared statement as parameters
    mysqli_stmt_bind_param($stmt, "s", $param_userID);
    
    // Set parameters
    $param_userID = $_SESSION['accountID'];
    
    // Attempt to execute the prepared statement
    if(mysqli_stmt_execute($stmt)){
        // Store result
        mysqli_stmt_store_result($stmt);
        
        mysqli_stmt_bind_result($stmt, $param_userImage);
        if(mysqli_stmt_fetch($stmt)){
            $profileImgDir = base64_decode($param_userImage);
        }
    }
    // Close statement
    mysqli_stmt_close($stmt);  

mysqli_close($link);    
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home Page</title>
<!-- include bootstrap --> 
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" rel="stylesheet">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!--Bootsrap 4 CDN-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
<!--Fontawesome CDN-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link rel="stylesheet" href="layout.php">
    <script src="https://www.google.com/recaptcha/api.js?render=6Lc7Cb0UAAAAAIMgxbAXd9kLcVhLPeapc8zsouu7"></script>    <style type="text/css">
        body{
            background-image: url(images/homePage.jpg);            
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            height: 100%;
            font: 14px sans-serif;
            text-align: center;

        }
        h3{ font: sans-serif; }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Welcome to our site.</h1>
    </div>
    <p>
        <a href="passwordReset.php" class="btn btn-warning">Reset Your Password</a>
        <a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a>
		<br>
		<br>
		<img src="<?php echo $profileImgDir; ?>" width="125" height="125">
    </p>
    <form id="search-form">
      <div class="page-header  justify-content-center row">
          <input id="search-bar" class="form-control col-md-8" placeholder="search">
      </div>
    </form>
    <div id="movie-cards" class ="row justify-content-center">
          
    </div>
</body>
<div id="movie-modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content row">
      <div class="modal-header col-12">
        <h3 class="modal-title text-secondary"></h3>
      </div>
      <div class="modal-body col-12">
          <h5 class="modal-summary"></h5>
      </div>
        <div class="modal-share col-12 row">
            <a class="modal-twitter-link col-4"><img class="logo" src="images/twitter.png"></a>
            <a class="modal-facebook-link col-4"><img class="logo" src="images/facebook.jpg"></a>
            <a class="modal-pinterest-link col-4"><img class="logo" src="images/pinterest.png"></a>
        </div>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    </div>
  </div>
</div>
    
<!-- include jquery, popper.js, and bootstrap js -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    
</html>

<script type=text/javascript>
    var usingSearch = false;
    var currentPage = 1;
    $(document).ready(function() {
        //counts the current list of popular movies
        populatePage(currentPage);        
        
        //call populatePage when scrolled to bottom of page
        $(window).scroll(function(){
           if($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
             currentPage++;
             if(usingSearch === true){
              console.log(currentPage);
              populatePageSearch(currentPage);
             }
             else{
               console.log(usingSearch);
              populatePage(currentPage);
             }
               
           }
        });
    });
    //call the API and append the html of the results
    function populatePage(currentPage){
        $.ajax('https://api.themoviedb.org/3/movie/popular?api_key=159d1f93f8f7827f36676bb412e6c3d6&language=en-US&page='+currentPage,{
            success: function (data, status, xhr) {// success callback function
                if(status !== "timeout"){
                    data['results'].forEach(function(item, index){
                    $("#movie-cards").append(
                        '<div class="movie-container m-4 col-l-3" style="width: 18rem; height: 33rem" >'+
                          '<img src="https://image.tmdb.org/t/p/original/'+item['poster_path']+'" class="movie-img" alt="Movie Poster">'+
                          '<div class="movie-title">'+
                            '<h3 class="text">'+item['title']+'</h5>'+
                          '<div>'+
                          '<div class="movie-summary" hidden>'+item['overview']+'</div>'+
                        '</div>'
                      );
                    });
                }
            }
        });
    }
    
    //call the API and append the html of the results
    function populatePageSearch(currentPage){
        let searchVal = $("#search-bar").val();
        searchVal = searchVal.replace(" ", "+");
        $.ajax('https://api.themoviedb.org/3/search/multi?api_key=159d1f93f8f7827f36676bb412e6c3d6&query='+searchVal+'&page='+currentPage,{
            success: function (data, status, xhr) {// success callback function
                if(status !== "timeout"){
                  data['results'].forEach(function(item, index){
                    //check if img exists
                    //$.ajax('https://image.tmdb.org/t/p/original/'+item['poster_path'],{
                    //  success: //display the card
                      $("#movie-cards").append(
                          '<div class="movie-container m-4 col-l-3" style="width: 18rem; height: 33rem" >'+
                            '<img src="https://image.tmdb.org/t/p/original/'+item['poster_path']+'" class="movie-img" alt="Movie Poster">'+
                            '<div class="movie-title">'+
                              '<h3 class="text-title">'+item['title']+'</h5>'+
                            '<div>'+
                            '<div class="movie-summary" hidden>'+item['overview']+'</div>'+
                          '</div>'
                      )
                    //});
                  });
                }
            }
        });
    }

    
    $("#movie-cards").on("click", ".movie-container", function(){
        let title = $(this).find('.movie-title').children().html();
        let summary = $(this).find('.movie-summary').html();
        let posterPath = $(this).find('.movie-img').attr("src");
        $('#movie-modal').modal('toggle');
        $('.modal-title').html(title);
        $('.modal-summary').html(summary);
        $('.modal-twitter-link').attr("href", "https://twitter.com/intent/tweet?text=Have you seen "+title+"? It's pretty sick! &hashtags=mediacart");
        $('.modal-facebook-link').attr("href", "https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fzneeley.com%2FMediaCart%2Flogin.php&quote=Have you seen "+title+"? It's pretty rad!");
        $('.modal-pinterest-link').attr("href", "https://pinterest.com/pin/create/button/?url=&media="+posterPath+"&description=Have you seen "+title+"? It's mega cool!");
        //make it open a new tab
        $('.modal-twitter-link').attr("target", "_blank");
        $('.modal-facebook-link').attr("target", "_blank");
        $('.modal-pinterest-link').attr("target", "_blank");

    });
    
    $("#search-form").submit(function(e){
      e.preventDefault();
      usingSearch = true;
      currentPage = 1;
      $("#movie-cards").empty();
      populatePageSearch();
      
    });
</script>