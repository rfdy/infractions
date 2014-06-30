<?php

namespace rfd\infractions\Service;

use Symfony\Component\Config\Definition\Exception\Exception;

class InfractionRulesManager {

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
     * @param $point_level
     * @param $group
     * @param $use_rank
     * @return mixed
     */
    public function add_rule($point_level, $group, $use_rank) {
        // escape input

        $sql_ary = array(
            'point_level'      => (int) $point_level,
            'group_id'            => (int) $group,
            'use_rank'     => (int) $use_rank,
        );


        $this->db->sql_return_on_error(true);
        $result = $this->db->sql_query(
            'INSERT INTO ' . $this->tables['rules'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary)
        );

        return $result;
    }

    /**
     * @param $id
     * @param $point_level
     * @param $group
     * @param $use_rank
     * @return mixed
     */
    public function edit_rule($id, $point_level, $group, $use_rank) {
        $sql_ary = array(
            'point_level'      => (int) $point_level,
            'group_id'            => (int) $group,
            'use_rank'     => (int) $use_rank,
        );
        $this->db->sql_return_on_error(true);
        $result = $this->db->sql_query(
            'UPDATE ' . $this->tables['rules'] . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
            WHERE point_level=' . $id
        );

        return $result;

    }

    /**
     * @param $point_level
     */
    public function delete_rule($point_level) {
        $this->db->sql_query(
            'DELETE FROM ' . $this->tables['rules'] . ' WHERE point_level=' . $point_level
        );
    }

    /**
     * @param int $low
     * @param int $high
     * @return array
     */
    public function getRules($low = 0, $high = 0) {
        $sql = 'SELECT * FROM ' . $this->tables['rules'];

        if ($high > 0) {
            $sql.= ' WHERE point_level>' . $low . ' AND point_level<=' . $high;
        }

        $sql.= ' ORDER BY point_level;';
        $result = $this->db->sql_query($sql);
        $rows = array();

        while ($row = $this->db->sql_fetchrow($result)) {
            $rows[] = $row;
        }

        $this->db->sql_freeresult($result);

        return $rows;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getRule($id) {
        $result = $this->db->sql_query('SELECT * FROM ' . $this->tables['rules'] . ' WHERE point_level = '. $id);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        return $row;
    }

    /**
     * @param bool $return_system
     * @return array
     */
    public function getGroups($return_system = false) {

        $sql = 'SELECT group_name, group_id, group_rank, group_type FROM ' . GROUPS_TABLE;

        if (!$return_system) {
            $sql .= ' WHERE group_type<>' . GROUP_SPECIAL;
        }

        $result = $this->db->sql_query($sql);
        $rows = array();

        while ($row = $this->db->sql_fetchrow($result)) {
            $rows[] = $row;
        }
        $this->db->sql_freeresult($result);

        return $rows;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getGroupName($id) {
        $result = $this->db->sql_query('SELECT group_name FROM ' . GROUPS_TABLE . ' WHERE GROUP_ID =' . $id);
        if ($row = $this->db->sql_fetchrow($result)) {
            $this->db->sql_freeresult($result);
            return $row['group_name'];
        }
    }
}


