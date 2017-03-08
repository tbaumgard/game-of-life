<?php

/**
 * A simple implementation of Conway's Game of Life.
 *
 * @see https://en.wikipedia.org/wiki/Game_of_life
 */
class GameOfLife
{

	/**
	 * The current generation of the world.
	 */
	private $generation;

	/**
	 * This is the width and height of the world in which the cells live.
	 */
	private $size;

	/**
	 * This is an array of all of the alive cells in the current generation. It's
	 * a two-dimensional array with the first dimension used for the zero-based
	 * row coordinate and the second dimension is used for the zero-based column
	 * coordinate. The values will always be set to @c true.
	 */
	private $aliveCells;

	/**
	 * Construct a new world of a given size and optionally with a given starting
	 * state.
	 *
	 * @param integer $size
	 *   width and height of the world, which can range from @c 3 to @c 100
	 * @param array $initialCells
	 *   array of the initial alive cell coordinates (zero-based), e.g.,
	 *   @code [[0, 0], [0, 1]] @endcode, or not given to use randomly chosen
	 *   coordinates
	 * @throw InvalidArgumentException
	 */
	public function __construct($size, $initialCells=null)
	{
		if (!is_integer($size) || $size < 3 || $size > 100) {
			throw new InvalidArgumentException("\$size must be >= 3 and <= 100");
		}

		$this->generation = 1;
		$this->size = $size;
		$this->aliveCells = [];

		if (func_num_args() > 1) {
			if (!is_array($initialCells)) {
				throw new InvalidArgumentException("\$initialCells must be an array");
			}

			foreach ($initialCells as $initialCell) {
				if (!is_array($initialCell)) {
					throw new InvalidArgumentException("Cells must be arrays");
				}

				if (!isset($initialCell[0]) || !isset($initialCell[1])) {
					throw new InvalidArgumentException("A cell is missing a coordinate");
				}

				if (!is_integer($initialCell[0]) || !is_integer($initialCell[1])) {
					throw new InvalidArgumentException("Coordinates must be integers");
				}

				if ($initialCell[0] < 0 || $initialCell[1] < 0) {
					throw new InvalidArgumentException("Coordinates must be >= 0");
				}

				if ($initialCell[0] >= $size || $initialCell[1] >= $size) {
					throw new InvalidArgumentException("Coordinates must be < \$size");
				}

				$row = $initialCell[0];
				$column = $initialCell[1];

				$this->aliveCells[$row][$column] = true;
			}
		} else {
			$numIterations = mt_rand(0, $size*$size);
			$coordinateMax = $size - 1;

			for ($i = 0; $i < $numIterations; $i++) {
				$row = mt_rand(0, $coordinateMax);
				$column = mt_rand(0, $coordinateMax);

				// This might overwrite previously set cells, but that doesn't really
				// matter since the cells are meant to be random anyway.
				$this->aliveCells[$row][$column] = true;
			}
		}
	}

	/**
	 * Advance the world to the next generation.
	 */
	public function advance()
	{
		// The only cells that need to be considered are all currently alive cells
		// and all currently dead cells that neighbor an alive cell.  The dead
		// neighbors are checked after checking all of the alive neighbors since
		// overlap is highly likely, and this reduces the amount of checks
		// necessary. It also makes the loop for alive cells simpler.

		$nextGeneration = [];
		$deadNeighbors = [];

		foreach ($this->aliveCells as $row => $columns) {
			foreach ($columns as $column => $ignore) {
				$neighbors = $this->neighbors([$row, $column]);

				foreach ($neighbors as $neighbor) {
					if (!$this->isCellAlive($neighbor)) {
						$deadNeighbors[$neighbor[0]][$neighbor[1]] = true;
					}
				}

				if ($this->willCellBeAlive([$row, $column], $neighbors)) {
					$nextGeneration[$row][$column] = true;
				}
			}
		}

		foreach ($deadNeighbors as $row => $columns) {
			foreach ($columns as $column => $ignore) {
				$neighbors = $this->neighbors([$row, $column]);

				if ($this->willCellBeAlive([$row, $column], $neighbors)) {
					$nextGeneration[$row][$column] = true;
				}
			}
		}

		$this->generation += 1;
		$this->aliveCells = $nextGeneration;
	}

	/**
	 * Get the current generation number.
	 *
	 * @retval integer
	 *   current generation number
	 */
	public function generation()
	{
		return $this->generation;
	}

	/**
	 * Get an array of the cells that are alive in the current generation of the
	 * world.
	 *
	 * @retval array
	 *   array of the coordinates (zero-based) of the alive cells in the current
	 *   generation of the world
	 */
	public function aliveCells()
	{
		$aliveCells = [];

		foreach ($this->aliveCells as $row => $columns) {
			foreach ($columns as $column => $ignore) {
				$aliveCells[] = [$row, $column];
			}
		}

		return $aliveCells;
	}

	/**
	 * Get a visual representation of the current generation of the world.
	 *
	 * @retval string
	 *   visual representation of the current generation of the world
	 */
	public function visualWorldRepresentation()
	{
		$horizontalDashes = str_repeat("━", $this->size);

		$world = "┏{$horizontalDashes}┓\n";

		for ($row = 0; $row < $this->size; $row++) {
			$world .= "┃";

			for ($column = 0; $column < $this->size; $column++) {
				$world .= $this->isCellAlive([$row, $column]) ? "•" : " ";
			}

			$world .= "┃\n";
		}

		$world .= "┗{$horizontalDashes}┛";

		return $world;
	}

	/**
	 * Create a new, randomly generated world and automatically display and
	 * advance the world either endlessly or for a given number of generations.
	 * This method will delay each round by around half a second.
	 *
	 * @param integer $size
	 *   width and height of the world, which can range from @c 3 to @c 100
	 * @param integer $numGenerations
	 *   optional number of generations to advance to and display
	 * @throw InvalidArgumentException
	 */
	public static function autoplay($size, $numGenerations=null)
	{
		$game = new GameOfLife($size);
		$numArgs = func_num_args();

		if ($numArgs > 1 && (!is_integer($numGenerations) || $numGenerations < 0)) {
			throw new InvalidArgumentException("\$numGenerations must be >= 0");
		}

		while ($numArgs == 1 || $numGenerations-- > 0) {
			echo $game->visualWorldRepresentation(), "\n";

			// Sleep a little so that mere humans can actually see what's happening in
			// each generation.
			usleep(500000);

			$game->advance();
		}
	}

	/**
	 * Determine whether or not a cell at a given position is alive in the current
	 * generation.
	 *
	 * @param array $cell
	 *   cell coordinates
	 * @retval boolean
	 *   whether or not the cell is alive in the current generation
	 */
	private function isCellAlive($cell)
	{
		return isset($this->aliveCells[$cell[0]][$cell[1]]);
	}

	/**
	 * Determine whether or not a cell at a given position will be alive in the
	 * next generation.
	 *
	 * @param array $cell
	 *   cell coordinates
	 * @param array $neighbors
	 *   array of neighbor cell coordinates
	 * @retval boolean
	 *   whether or not the cell will be alive in the next generation
	 */
	private function willCellBeAlive($cell, $neighbors)
	{
		// https://en.wikipedia.org/wiki/Game_of_life#Rules
		// The rules reduce to:
		// 1. Any cell with three alive neighbors will be alive in the next
		//    generation.
		// 2. Any alive cell with two alive neighbors will be alive in the next
		//		state.

		$numAliveNeighbors = $this->countAliveNeighbors($neighbors);

		if ($numAliveNeighbors == 3) {
			return true;
		}

		return $numAliveNeighbors == 2 && $this->isCellAlive($cell);
	}

	/**
	 * Get the coordinates of the neighbors of a cell at a given position.
	 *
	 * @param array $cell
	 *   cell coordinates
	 * @retval array
	 *   array of neighbor cell coordinates with the row coordinate in position
	 *   @c 0 and the column coordinate at position @c 1 for each pair of
	 *   coordinates
	 */
	private function neighbors($cell)
	{
		$neighbors = [];

		for ($rowOffset = -1; $rowOffset <= 1; $rowOffset++) {
			for ($columnOffset = -1; $columnOffset <= 1; $columnOffset++) {
				// A cell can't be a neighbor of itself.
				if ($rowOffset == 0 && $columnOffset == 0) {
					continue;
				}

				$row = $this->constrainCoordinate($cell[0] + $rowOffset);
				$column = $this->constrainCoordinate($cell[1] + $columnOffset);

				$neighbors[] = [$row, $column];
			}
		}

		return $neighbors;
	}

	/**
	 * Count how many alive neighbors there are for a cell.
	 *
	 * @param array $neighbors
	 *   array of neighbor cell coordinates
	 * @retval integer
	 *   number of alive neighbors of the cell
	 */
	private function countAliveNeighbors($neighbors)
	{
		$numAliveNeighbors = 0;

		foreach ($neighbors as $neighbor) {
			if ($this->isCellAlive($neighbor)) {
				$numAliveNeighbors += 1;
			}
		}

		return $numAliveNeighbors;
	}

	/**
	 * Constrain a coordinate to within the boundaries of the world. When a
	 * coordinate is outside of the boundaries of the world, it will be wrapped
	 * around to the other side of it.
	 *
	 * @param integer $coordinate
	 *   row or column coordinate
	 * @retval integer
	 *   constrained coordinate value
	 */
	private function constrainCoordinate($coordinate)
	{
		$remainder = $coordinate % $this->size;

		return $coordinate < 0 ? $this->size + $remainder : $remainder;
	}

}
