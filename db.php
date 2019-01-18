<?php
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "fishing";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }
function getFishData($game_id) {
	global $conn;
	$fishSql = 'SELECT f.* FROM `fish` AS f 
	LEFT JOIN caught_fish AS c ON c.fish_id = f.fish_id AND game_id = :game_id
	WHERE c.caught_fish_id is NULL';
	$fishStmt =  $conn->prepare($fishSql);
	$fishStmt->execute([
		':game_id' => $game_id
	]);
	$randFish = $fishStmt->fetchAll(PDO::FETCH_ASSOC);
	return $randFish;
}
function getGameData() {
	global $conn;
	$gameSql = "SELECT * 
		FROM game
		ORDER BY game_id desc 
		LIMIT 1";
	$gameStmt = $conn->prepare($gameSql);
	$gameStmt->execute();
	$game = $gameStmt->fetch(PDO::FETCH_ASSOC);
	return $game;
}
function getCaughtFishData($game_id) {
	global $conn;
	$caughtFishSql = 'SELECT c.caught_fish_id, c.fish_id, f.fish_name, f.fish_strength 
			FROM caught_fish AS c 
			JOIN fish AS f 
			ON c.fish_id = f.fish_id
			WHERE game_id = :game_id';
	$caughtFishStmt = $conn->prepare($caughtFishSql);
	$caughtFishStmt->execute([
		':game_id' => $game_id
	]);
	$caughtFishData = $caughtFishStmt->fetchAll(PDO::FETCH_ASSOC);
	return $caughtFishData;
}
function cancelGame() {
	global $conn;
	$cancelCurrentSql = "UPDATE game
		SET game_status = 'cancelled'
		WHERE game_status = 'started'";
	$cancelCurrentStmt = $conn->prepare($cancelCurrentSql);
	$cancelCurrentStmt->execute();	
}
function startGame() {
	global $conn;
	$startGameSql = "INSERT INTO game (game_fishing_line_strength, game_target_score, game_score, game_lives_remaining, game_status)
		VALUES (:GAME_FISHING_LINE_STRENGTH, :GAME_TARGET_SCORE, 0, :GAME_LIVES_REMAINING, 'started')";
	$startGameStmt = $conn->prepare($startGameSql);
	$startGameStmt->execute([
		':GAME_FISHING_LINE_STRENGTH' => GAME_FISHING_LINE_STRENGTH,
		':GAME_TARGET_SCORE' => GAME_TARGET_SCORE,
		':GAME_LIVES_REMAINING' => GAME_LIVES_REMAINING
	]);
}
function catchFish($fish_id, $game_id) {
	global $conn;
	$appendFishSql = "INSERT INTO caught_fish (fish_id, game_id)
		VALUES (:FISH_ID, :GAME_ID)";
	$appendFishStmt = $conn->prepare($appendFishSql);
	$appendFishStmt->execute([
		':FISH_ID' => $fish_id,
		':GAME_ID' => $game_id
	]);
}
function quitGame($game_id) {
	global $conn;
	$updateSql = "UPDATE game
		SET game_status = 'quit'
		WHERE game_id = :game_id";
	$updateStmt = $conn->prepare($updateSql);
	$updateStmt->execute([
		':game_id' => $game_id
	]);
}
function updateGameDetails($game) {
	global $conn;
	$updateGameSql = "UPDATE game
		SET game_status = :game_status, game_score = :game_score, game_lives_remaining = :game_lives_remaining
		WHERE game_id = :game_id";
	$updateGameStmt = $conn->prepare($updateGameSql);
	$updateGameStmt->execute([
		':game_id' => $game['game_id'],
		':game_status' => $game['game_status'],
		':game_score' => $game['game_score'],
		':game_lives_remaining' => $game['game_lives_remaining']
	]);
}
?>