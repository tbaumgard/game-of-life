# Game of Life

This project contains the `GameOfLife` class, which is a simple implementation of [Conway's Game of Life](https://en.wikipedia.org/wiki/Game_of_life) in PHP.

## Example Usage

A quick and easy way to play around with `GameOfLife` is to run the following command in the same directory as the source file:

```sh
php -r 'require "GameOfLife.php"; GameOfLife::autoplay(20);'
```

This will create a randomly generated world that's 20 cells by 20 cells, and it will print each generation until interrupted by pressing `CTRL+C`. The `GameofLife::autoplay()` method also accepts a limit on the number of generations as its second argument.

The following is an simple example of how to use the class from code:

```php
$size = 20;
$initialCells = [];
$numGeneratations = 20;

$game = new GameOfLife($size, $initialCells);

for ($i = 0; $i < $numGenerations; $i++) {
	$generation = $game->generation();
	$aliveCells = $game->aliveCells();
	$visualRepresentation = $game->visualWorldRepresentation();

	// Do something . . .

	$game->advance();
}
```

## Example Patterns

[Some interesting patterns](https://en.wikipedia.org/wiki/Conway%27s_Game_of_Life#Examples_of_patterns) can result during Conway's Game of Life. Assuming a world that's 20 cells by 20 cells, the following arrays of initial cells create a few examples of these:

```php
// https://en.wikipedia.org/wiki/Glider_(Conway%27s_Life)
$glider = [[10, 11], [11, 12], [12, 10], [12, 11], [12, 12]];

// https://en.wikipedia.org/wiki/Spaceship_(cellular_automaton)
$spaceship = [[11, 12], [11, 15], [12, 11], [13, 11], [13, 15], [14, 11], [14, 12], [14, 13], [14, 14]];
```

## A Note on Fonts

Generations may look disproportionate when displayed due to the font settings used to display them. This is typically due to the line spacing, and reducing it can help make the generated worlds look square.
