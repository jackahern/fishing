<?php
	include_once('db.php');
	session_start();
	$action = null;
	$msg = null;
	// TODO: create a select query to get all the fishes from the fish table, and end up with a $randFish array
	$fishSql = 'SELECT * FROM fish';
	$fishStmt =  $conn->prepare($fishSql);
	$fishStmt->execute();
	$randFish = $fishStmt->fetchAll();
	// TODO: create a select query from the caught_fish table, that joins onto the fish table, in order to get the fish data
	$caughtFishSql = 'SELECT c.caught_fish_id, c.fish_id, f.fish_name, f.fish_strength 
			FROM caught_fish AS c 
			JOIN fish AS f 
			ON c.fish_id = f.fish_id';
	$caughtFishStmt = $conn->prepare($caughtFishSql);
	$caughtFishStmt->execute();
	$fishData = $caughtFishStmt->fetchAll();
	// TODO: create a select query to get the game data, as a single result, and end up with a $game array
	$gameSql = 'SELECT * FROM game';
	$gameStmt = $conn->prepare($gameSql);
	$gameStmt->execute();
	$game = $gameStmt->fetch();
	if (isset($_POST['action'])) {
		$action = $_POST['action'];
	} 
	if (!isset($game['status'])) {
		$game['status'] = 'not started';
	}
	if ($action	== 'restart') {
		$action = 'start_game';
	} 
	if (($game['status'] == 'win' || $game['status'] == 'lose') && !in_array($action, ['start_game', 'quit_game'])) {
	$msg = 'The game has already been played, please start a new game, restart or quit';
	//$action = 'game_over';
	}	
	else if ($action == 'start_game') {
	// Start of game setup
		$msg = 'New game started';
		// TODO: when starting a new game, an insert query must be used to store the game data in the game table, to replace the session data below
		$startGameSql = "INSERT INTO game (game_fishing_line_strength, game_target_score, game_score, game_lives_remaining, game_status)
			VALUES (4, 36, 0, 3, 'started')";
		$startGameStmt = $conn->prepare($startGameSql);
		$startGameStmt->execute();
	//End of game setup
	} 
	else if($action == 'fishing') {
		// Game functionality
		$startGameSql['game_status'] = 'started';
		$fishRandId = array_rand($randFish, 1);
		$fishCaught = $randFish[$fishRandId];
		// To be used as a short reference to the 3rd dimension of the array
		$caughtFishInfo = $randFish[$fishCaught];
		// remove fish from fish available in pond - for some reason the variable of $caughtFishInfo doesn't work when used here?
		$randFish[$fishCaught]['amount']--;
		unset($randFish[$fishRandId]);
		// find out if i succesfully caught the fish or if it broke my line
		if ($caughtFishInfo['strength'] <= $startGameSql['game_fishing_line_strength']) {
			//successful fish catch
			$startGameSql['game_score'] += $caughtFishInfo['strength'];
			$_SESSION['caughtFishes'][] = $fishCaught;
			$msg = $fishCaught . ' ' . $caughtFishInfo['amount'];
		} 
		else {	
			//fish broke the line
			$startGameSql['game_lives_remaining']--;
			$msg = $fishCaught . ' broke the line';
		}
		// now check if i have any lives left
		if ($startGameSql['game_lives_remaining'] === 0) {
			$startGameSql['game_status'] = 'lose';
			$msg = 'You lose';
		}
		if ($startGameSql['game_score'] >= $startGameSql['game_target_score']) {
			$startGameSql['game_status'] = 'win';
			$msg = 'You won';
		}
	} 
	else if ($action == 'quit_game') {
		$action = 'start_game';
		$startGameSql['game_status'] = 'not started';
		$updateSql = "UPDATE game SET game_status = 'not_started'";
		$updateStmt = $pdo->prepare($updateSql);
		$updateStmt->execute();
		// TODO: use an update query, to update the status of the game, remember to either reset the $game variable, or redirect the user back to the page so it grabs fresh data at the top
	}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="fishing-html5.css">
	<title>Fishing Game</title>
</head>
<body>
	<?php 
	if ($_SESSION['status'] == 'not started') { 
		?>
		<h1>Welcome to Simitive's fishing game</h1>
		<h3>Click the 'Start new game' button to start playing!</h3>
		<?php	
	} 
	if ($_SESSION['status'] == 'started' || $_SESSION['status'] == 'win' || $_SESSION['status'] == 'lose') { 
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
					foreach ($_SESSION['randFish'] as $fish) { ?>
						<?= '<li class="remaining-fish ' . strtolower($fish) . '">' . $fish . ':' . $_SESSION['fishes'][$fish]['strength'] . '<br>' . '</li>';
					}
					?>
					</ul>
				</div>
				<div id="middle" class="section-item">
					<h1>Fish caught</h1>
					<?php
					if (empty($_SESSION['caughtFishes'])) {
						?>
						No fish have been caught yet
						<?php	
					} 
					else {
						foreach ($_SESSION['caughtFishes'] as $caughtFish) { 
							$caughtFishInfo = $_SESSION['fishes'][$caughtFish];
							?>
							<?= '<li class="remaining-fish ' . strtolower($caughtFish) . '">' . $caughtFish . ' ' . $_SESSION['fishes'][$caughtFish]['strength'] . '<br>' . '</li>';
						}	
					}
					?>
				</div>
			</section>
			<aside id="right" class="game-stats">
				<h2>Game statistics</h2>
				<p>
					<strong>Game score: </strong>
					<?= $_SESSION['gameScore']; ?>
				</p>
				<p>
					<strong>Target score: </strong>
					<?= $_SESSION['targetScore']; ?> 
				</p>
				<p>
					<strong>Fishing line strength: </strong>
					<?= $_SESSION['fishingLineStrength']; ?> 
				</p>
				<p>
					<strong>Remaining lives: </strong>
					<?= $_SESSION['lives']; ?> 
				</p>
				<h2>Play</h2>
				<?php
				}
				?> 
				<form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>">
					<?php 
					if ($_SESSION['status'] == 'started') { 
						?>
						<button type="submit" name="action" value="fishing">Go Fish</button>
						<?php
					}
					if ($_SESSION['status'] == 'started'  || $_SESSION['status'] == 'win' || $_SESSION['status'] == 'lose') { 
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
