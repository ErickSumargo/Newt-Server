<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
	<title>Newt | Solve at Best</title>
    <link rel="shortcut icon" href="website/images/logo.ico">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="/website/css/app.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="/website/js/app.js"></script>
</head>
<body>
    <div class="loader"></div>
	<div class="row main-container">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 smartphone-container text-center">
            <img id="smartphone" src="/website/images/smartphones.png">
        </div>
        
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 content-container">
            <div class="row">
                <div class="col-xs-5 col-sm-5 col-md-5 col-lg-5 text-center">
                    <img id="icon" src="/website/images/ic_best_teacher.png"><br>
                    <span>Best Teacher</span>
                </div>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <span id="plus">+</span>
                </div>
                <div class="col-xs-5 col-sm-5 col-md-5 col-lg-5 text-center">
                    <img id="icon" src="/website/images/ic_live_chat.png"><br>
                    <span>Live Chat</span>
                </div>
            </div>
            
            <h1>TIADA BATASAN</h1>
            <div class="problem-container">
                <h1 id="problem">LOKASI</h1>
                <h1 id="problem">WAKTU</h1>
                <h1 id="problem">BIAYA MAHAL</h1>
            </div>
            <h1>UNTUK KEGIATAN</h1>
            <h1>BELAJAR TAMBAHAN</h1>
            
            <div class="note-container">
                <h1 id="note">MUST-HAVE APP <br>UNTUK MURID SEKOLAH</h1>
            </div>
            <div class="playstore-container">
                <a href="https://play.google.com/store/apps/details?id=app.newt.id">
                    <img id="playstore" src="/website/images/playstore.png">
                </a>
            </div>
        </div>
	</div>
</body>
</html>