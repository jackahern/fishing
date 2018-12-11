<?php
	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
	include_once('db.php');
	include_once('functions.php');
	define('GAME_FISHING_LINE_STRENGTH', rand(1, 4));
	define('GAME_TARGET_SCORE', rand(10, 40));
	define('GAME_LIVES_REMAINING', 3);
	session_start();
	$action = null;
	$msg = null;
	$game = getGameData();
	$randFish = getFishData($game['game_id']);
	$caughtFishData = getCaughtFishData($game['game_id']);
	if (isset($_POST['action'])) {
		$action = $_POST['action'];
	} 
	if (!isset($game['game_status'])) {
		$game['game_status'] = 'not started';
	}
	if ($action	== 'restart') {
		$action = 'start_game';
	} 
	if (($game['game_status'] == 'won' || $game['game_status'] == 'lost') && !in_array($action, ['start_game', 'quit_game'])) {
	$msg = 'The game has already been played, please start a new game, restart or quit';
	}	
	else if ($action == 'start_game') {
		// Start of game setup
		$msg = 'New game started';
		cancelGame();		
		startGame();
		redirect($msg);
		//End of game setup
	} 
	else if($action == 'fishing') {
		// Game functionality
		$game['game_status'] = 'started';
		$fishRandId = array_rand($randFish, 1);
		$fishCaught = $randFish[$fishRandId];
		// find out if i succesfully caught the fish or if it broke my line
		if ($fishCaught['fish_name'] == 'Shark') {
			$game['game_status'] = 'lost';
			$msg = 'You were eaten by a shark!';
			$game['game_lives_remaining'] = 0;
		}
		else if ($fishCaught['fish_strength'] <= $game['game_fishing_line_strength']) {
			//successful fish catch
			catchFish($fishCaught['fish_id'], $game['game_id']);
			$game['game_score'] += $fishCaught['fish_strength'];
			$msg = $fishCaught['fish_name'] . ' ' . $fishCaught['fish_strength'];
		}
		else {	
			//fish broke the line
			$game['game_lives_remaining']--;
			$msg = $fishCaught['fish_name'] . ' broke the line';
		}
		if ($game['game_score'] >= $game['game_target_score']) {
			$game['game_status'] = 'won';
			$msg = 'You won';
		}
		if ($game['game_lives_remaining'] === 0) {
			$game['game_status'] = 'lost';
			$msg = 'You lose';
		}
		updateGameDetails($game);
		redirect($msg);
	} 
	else if ($action == 'quit_game') {
		$action = 'start_game';
		$game['game_status'] = 'not started';
		quitGame($game['game_id']);
		header("Location: fishing-with-html5.php");
		die();
	}
	if (isset($_SESSION['msg'])) {
		$msg = $_SESSION['msg'];
		unset($_SESSION['msg']);
	}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="fishing-advanced-css.css">
	<title>Fishing Game</title>
</head>
<body>
	<?php 
	if ($game['game_status'] == 'not started' || $game['game_status'] == 'quit') { 
		?>
		<h1>Welcome to Simitive's fishing game</h1>
		<h3>Click the 'Start new game' button to start playing!</h3>
		<?php	
	} 
	if ($game['game_status'] == 'started' || $game['game_status'] == 'won' || $game['game_status'] == 'lost') { 
		?>
		<h1>
			<?= $msg ?>
		</h1>
		<main>
			<section id="ponds">
				<div id="left" class="section-item">
					<h1>Remaining fish</h1>
					<ul>	
					<?php
					foreach ($randFish as $fish) { 
						?>
						<li class="remaining-fish <?=strtolower($fish['fish_name'])?>">
							<?= $fish['fish_name'] . ':' . $fish['fish_strength']; ?>
							<br>
						</li>
						<?php
					}
					?>
					</ul>
				</div>
				<div id="middle" class="section-item">
					<h1>Fish caught</h1>
					<?php
					if (empty($caughtFishData)) {
						?>
						No fish have been caught yet
						<?php	
					} 
					else {
						foreach ($caughtFishData as $caughtFish) {
							?>
							<li class="remaining-fish <?=strtolower($caughtFish['fish_name'])?>">
								<?= $caughtFish['fish_name'] . ' ' . $caughtFish['fish_strength']; ?>
								<br>
							</li>
							<?php
						}	
					}
					?>
				</div>
			</section>
			<aside id="right" class="game-stats">
				<h2>Game statistics</h2>
				<p>
					<strong>Game score: </strong>
					<?= $game['game_score']; ?>
				</p>
				<p>
					<strong>Target score: </strong>
					<?= $game['game_target_score']; ?> 
				</p>
				<p>
					<strong>Fishing line strength: </strong>
					<?= $game['game_fishing_line_strength']; ?> 
				</p>
				<p>
					<strong>Remaining lives: </strong>
					<?= $game['game_lives_remaining']; ?> 
				</p>
				<h2>Play</h2>
				<?php
				}
				?> 
				<form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>">
					<?php 
					if ($game['game_status'] == 'started') { 
						?>
							<button type="submit" name="action" value="fishing">Go Fish</button>
						<?php
					}
					if ($game['game_status'] == 'started'  || $game['game_status'] == 'won' || $game['game_status'] == 'lost') { 
						?>
						<button type="submit" name="action" value="restart">Restart</button>
						<button type="submit" name="action" value="quit_game">Quit</button>
						<?php
					}
						?>
					<button type="submit" name="action" value="start_game">Start new game</button>
				</form>
			</aside>
		</main>
</body>
</html>