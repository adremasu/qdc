<?php
class Stopwatch
{
    /** @var mysqli */
    private $mysqli;
    /** @var int */
    private $stopwatch_id;
    /**
     * Stopwatch constructor
     * @param mysqli $mysqli
     * @param $stopwatch_id
     */
    public function __construct(\mysqli $mysqli, $stopwatch_id){
        $this->mysqli = $mysqli;
        $this->stopwatch_id = intval($stopwatch_id);
    }

    public function start() {
        $timestamp = time();
        $query = "
            INSERT INTO  `stopwatch` (`chat_id`, `timestamp`)
            VALUES ('$this->stopwatch_id', '$timestamp')
            ON DUPLICATE KEY UPDATE timestamp = '$timestamp'
        ";
        return $this->mysqli->query($query);
    }
    /**
     * Delete row with stopwatch id
     * @return bool|mysqli_result
     */
    public function stop(){
    $query = "
        DELETE FROM `stopwatch`
        WHERE `chat_id` = $this->stopwatch_id
        ";
        return $this->mysqli->query($query);
    }
}
?>
