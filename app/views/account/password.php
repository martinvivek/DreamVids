<div class="content">

	<section class="profile">
		<h1 class="title">Espace membre</h1>

		<nav class="tabs four">
			<ul>
				<li><a href="<?php echo WEBROOT.'account'; ?>">Mon compte</a></li>
				<li class="current"><a href="<?php echo WEBROOT.'account/password'; ?>">Mot de passe</a></li>
				<li><a href="<?php echo WEBROOT.'account/videos'; ?>">Mes vidéos</a></li>
				<li><a href="<?php echo WEBROOT.'channels'; ?>">Chaînes</a></li>
				<li><a href="<?php echo WEBROOT.'account/messages'; ?>">Messagerie</a></li>
			</ul>
		</nav>

		<?php if(isset($error)) { ?>
			<p style="color: #f00;"><?php echo $error ?></p>
			<br>
		<?php } ?>

		<?php if(isset($success)) { ?>
			<p style="color: #0f0;"><?php echo $success ?></p>
			<br>
		<?php } ?>

		<form class="form" method="post" action="">
			<label for="currentPass">Mot de passe actuel :</label>
			<input type="password" name="currentPass"><br />

			<label for="newPass">Nouveau mot de passe :</label>
			<input type="password" name="newPass"><br />

			<label for="newPassConfirm">Confirmation du nouveau mot de passe :</label>
			<input type="password" name="newPassConfirm"><br />

			<input type="submit" name="passwordSubmit" value="Enregistrer">
		</form>
	</section>

</div>