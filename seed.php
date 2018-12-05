<?php
	include_once('db.php');

	// TODO: truncate all tables
	$conn->prepare("TRUNCATE TABLE fish")->execute();
	$conn->prepare("TRUNCATE TABLE game")->execute();
	$conn->prepare("TRUNCATE TABLE caught_fish")->execute();

	echo "Truncated fish, game, caught fish<br>";

	$fishes = [
		"Mackeral" => [
			"amount" => 10,
			"strength" => 1
		],
		"Bass" => [
			"amount" => 8,
			"strength" => 2
		],
		"Cod" => [
			"amount" => 6,
			"strength" => 3
		],
		"Tuna" => [
			"amount" => 4,
			"strength" => 4
		],
		"Shark" => [
			"amount" => 1,
			"strength" => 5
		]
	];
	$randFish = [];
	foreach ($fishes as $fish => $fishStats) {
		for ($i=0; $i < $fishStats['amount']; $i++) { 
			$randFish[] = [
					"name" => $fish,
					"strength" => $fishStats['strength']
			];
		}
	}
	shuffle($randFish);

	$run = $_REQUEST['run'] ?? NULL;

	if ($run == 1) {
		foreach ($randFish as $fishStats) {
			$sql = "INSERT INTO fish (fish_name, fish_strength)
			VALUES (:fish_name, :fish_strength)";
			$stmt = $conn->prepare($sql);
			$stmt->execute([
				':fish_name' => $fishStats['name'],
				':fish_strength' => $fishStats['strength']
			]);
		}

		echo "Inserted " . count($randFish) . " fish<br>";

		// TODO: insert game options
		$fishing_line_strength = 4;
		$lives_remaining = 3;
		$target_score = 30;
		$sql = "INSERT INTO game (game_fishing_line_strength, game_lives_remaining, game_target_score)
		VALUES (:fishing_line_strength, :remaining_lives, :target_score)";
		$stmt = $conn->prepare($sql);
		$stmt->execute([
			':fishing_line_strength' => 4,
			':remaining_lives' => 3,
			':target_score' => 30
		]);

		echo "Inserted game data";
	}