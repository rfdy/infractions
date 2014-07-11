<?php

namespace rfd\infractions\Service;

use Symfony\Component\Config\Definition\Exception\Exception;

class InfractionManager {

    /**
     * Database driver
     * @var \phpbb\db\driver\driver
     */
    protected $db;

    /**
     * Name of the infraction database table
     * @var string
     */
    private $tables;

    public function __construct(\phpbb\db\driver\driver $db, array $tables) {
        $this->db           = $db;
        $this->tables = $tables;
    }

    /**
     * Add an Infraction to the database
     *
     * @param string $title     Title of the infraction (255 char string max)
     * @param int $points       The number of points that will be given to the user when issued this infraction
     * @param int $expires_days The number of days the Infraction will last
     * @return multi              The id of the newly created infraction, false otherwise
     */
    public function add_infraction($title, $points, $expires_days) {
        $sql_ary = array(
            'title'             => $title,
            'points'            => (int) $points,
            'expires_days'      => (int) $expires_days,
        );

        $this->db->sql_query(
            'INSERT INTO ' . $this->tables['infractions'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary)
        );

        return $this->db->sql_nextid();
    }

    /**
     * @param int $infraction_id
     * @param int $points       The number of points that will be given to the user when issued this infraction
     * @param int $expires_days The number of days the point penalty will be enforced
     *
     * @return boolean          Return true iff Infraction was edited successfully
     */
    public function edit_infraction($infraction_id, $title, $points, $expires_days) {
        $sql_ary = array(
            'title'             => $title,
            'points'            => (int) $points,
            'expires_days'      => (int) $expires_days,
        );

        $this->db->sql_query(
            'UPDATE ' . $this->tables['infractions'] . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
            WHERE infraction_id=' . $infraction_id
        );

        return true;
    }

    public function delete_infraction($infraction_id) {
        $this->db->sql_query(
            'DELETE FROM ' . $this->tables['infractions'] . ' WHERE infraction_id=' . $infraction_id
        );
    }

    /**
     * Return a single infraction for the given ID.
     * Return false if the infraction isn't found.
     * @param $infraction_id
     */
    public function get_infraction($infraction_id) {
        $result = $this->db->sql_query('SELECT * FROM ' . $this->tables['infractions'] . '
            WHERE infraction_id=' . $infraction_id);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }


    /**
     * Return an array of infractions ordered by title.
     *
     * @return array
     */
    public function get_infractions() {
        $result = $this->db->sql_query('SELECT * FROM ' . $this->tables['infractions'] . ' ORDER BY points, title DESC');
        $rows = array();

        while ($row = $this->db->sql_fetchrow($result)) {
            $rows[] = $row;
        }

        $this->db->sql_freeresult($result);
        return $rows;
    }
}


