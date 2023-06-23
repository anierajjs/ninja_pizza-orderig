<head>
	<!-- Compiled and minified CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
  <style type="text/css">
	  .brand{
	  	background: #cbb09c !important;
	  }
  	.brand-text {
  		color: #cbb09c !important;
  	}
		.navbar-dark .navbar-nav .nav-link {
	  color: rgba(255,255,255,.55);
		}
  	form{
  		max-width: 460px;
  		margin: 20px auto;
  		padding: 20px;
  	}
    .pizza{
      width: 100px;
      margin: 40px auto -30px;
      display: block;
      position: relative;
      top: -30px;
    }
		nav .brand-logo {
		 position:inherit;
		 padding-right: .5rem;
		 padding-left: .5rem;
		}
    .acc {
      text-align: right;
      margin-right: 5%;
    }

  </style>

</head>
<body class="grey lighten-4">
	<nav class="white z-depth-0">
		<div class="container">
      <ul>
      	<li><a href="index.php" class="brand-logo brand-text" >Ninja Pizza</a></li>
          <?php if ($_SESSION['usertype'] === 'admin' || $_SESSION['usertype'] === 'main admin') { ?>
          	<li ><a href="audit_logs.php" class="brand-logo brand-text"><small>Logs</small></a></li>
          	<li><a href="users.php" class="brand-logo brand-text"><small>Users</small></a></li>
          <?php } ?>
      </ul>

      <ul id="nav-mobile" class="right hide-on-small-and-down">
        <li><a href="add.php" class="btn brand z-depth-0">Add a Pizza</a></li>
      </ul>
		</div>
  </nav>

<div class="acc">
        <a href="logout.php">Logout <?php echo $_SESSION['username']; ?></a>
</div>
