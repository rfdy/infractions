<?php

namespace rfd\infractions\Service;

use Symfony\Component\Config\Definition\Exception\Exception;

class UserInfractionsManager {
    const STATUS_ACTIVE     = 0;
    const STATUS_EXPIRED    = 1;
    const STATUS_CANCELLED  = 2;
    const IS_WARNING        = 0;
    const SECONDS_IN_DAY    = 86400;

    /**
     * Database driver
     * @var \phpbb\db\driver\driver
     */
    protected $db;

    /**
     * Name of the infraction database table
     * @var string
     */
    private $rules_manager;
    private $tables;

    public function __construct($rules_manager, \phpbb\db\driver\driver $db, array $tables) {
        $this->rules_manager = $rules_manager;
        $this->db           = $db;
        $this->tables = $tables;
    }

    /**
     * Issues infraction to user, adds to user's total infraction points, and changes group and rank when necessary.
     *
     * @param int $moderator_id - the id of the moderator who issued the infraction
     * @param int $user_id - the id of the user who is receiving the infraction
     * @param int $post_id - a specific post that the user is receiving the infraction for
     * @param int $topic_id - the topic of the post that the user is receiving the infraction for
     * @param int $date_created - the date when the infraction was issued
     * @param string $title - the title of a message which is sent to the user receiving the infraction
     * @param string $reason - a message specifying why the user received the infraction
     * @param int $infraction_id - the id of the default Infraction selected by the moderator
     * @param int $points - the amount of points this infraction gives to the user
     * @param int $expires_days - the amount of days that the user will face the point penalty
     * @param int $status - the status of the infraction (either 0 for 'Active', 1 for 'Expired' or 2 for 'Cancelled'
     *
     * @return int - id of the newly created User Infraction
     */

    public function issue_infraction($moderator_id, $user_id, $post_id, $topic_id, $date_created, $title, $reason, $infraction_id, $points, $expires_days) {

        if (!function_exists('group_user_del')) {
            include __DIR__ . '/../../../../includes/functions_user.php';
        }

        $user = $this->get_user($user_id);
        $low = $user["user_infraction_points"];
        $high = $user['user_infraction_points'] + $points;
        $rank = $user['user_rank'];


        $this->add_user_infraction($moderator_id, $user_id, $post_id, $topic_id, $date_created, $title, $reason, $infraction_id, $points, $expires_days);
        $this->add_user_points($user_id, $points);

        $rules = $this->rules_manager->getRules($low, $high);

        foreach ($rules as $rule) {
               $this->add_user_rule($user_id, $rank, $rule);
               group_user_add($rule['group_id'], $user_id);

               if ($rule['use_rank']) {
                   $rank = $this->get_group_rank($rule['group_id']);
                   $this->edit_user_rank($user_id, $rank);
               }
        }
    }

    /**
     * Sets an infraction to expired
     *
     * @param $infraction_id
     * @return bool
     */
    public function expire_user_infraction($infraction_id) {
        return $this->remove_user_infraction($infraction_id, self::STATUS_EXPIRED);
    }

    /**
     * Sets an infraction to cancelled
     *
     * @param $infraction_id
     * @return bool
     */
    public function cancel_user_infraction($infraction_id) {
        return $this->remove_user_infraction($infraction_id, self::STATUS_CANCELLED);
    }

    /**
     * @param $post_id      Post to check
     * @return bool|mixed
     */

    public function post_has_infracted($post_id){
        if (!$post_id) {
            return false;
        }

        $sql = 'SELECT * FROM ' . $this->tables['inf_users'] . ' WHERE post_id=' . $post_id;
        $results = $this->db->sql_query($sql);

        return $this->db->sql_fetchrow($results);
    }

    /**
     *  Recalculates users' rank and groups based off of active issued infractions.
     *  Use to apply new or remove existing infraction rules/thresholds to users.
     *  No rollback possible once ran.
     */
    public function resynchronize() {

        $result = $this->db->sql_query('SELECT user_id from ' . USERS_TABLE);
        $rules = $this->rules_manager->getRules();
        $users = array();

        while ($row = $this->db->sql_fetchrow($result)) {
            $users[] = $row;
        }

        $this->db->sql_freeresult($result);

        foreach ($users as $user) {
            $this->reset_user_points($user['user_id']);
            $rank = $user['user_rank'];

            $result = $this->db->sql_query('SELECT SUM(points) AS total_points FROM ' . $this->tables['inf_users'] .
                ' WHERE user_id=' . $user['user_id'] . ' AND status=' . self::STATUS_ACTIVE);

            $row = $this->db->sql_fetchrow($result);

            $points = $row['total_points'];

            $this->db->sql_freeresult($result);

            $this->add_user_points($user['user_id'], $points);

            foreach ($rules as $rule) {
                if ($rule['point_level'] <= $points) {
                    group_user_add($rule['group_id'], $user['user_id']);

                    $this->add_user_rule($user['user_id'], $rank, $rule);

                    if ($rule['use_rank']) {
                        $rank = $this->get_group_rank($rule['group_id']);
                        $this->edit_user_rank($user['user_id'], $rank);
                    }
                }
            }
        }
    }

    /**
     * @param $time
     * @return array
     */
    public function get_expired_infractions($time) {

       $result = $this->db->sql_query('SELECT * FROM ' . $this->tables['inf_users'] . ' WHERE status=' . self::STATUS_ACTIVE . ' AND (date_created + expires_days*' . self::SECONDS_IN_DAY .') < ' . $time . ' AND expires_days<>0');
       $rows = array();

        while ($row = $this->db->sql_fetchrow($result)) {
            $rows[] = $row;
        }
        $this->db->sql_freeresult($result);

        return $rows;
    }

    public function get_users_infractions_count($user_id = false) {

        $sql = 'SELECT count(*) as cnt FROM ' . $this->tables['inf_users'];
        if ($user_id) {
            $sql .= " where user_id={$user_id}";
        }

        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);

        return $row['cnt'];
    }
    /**
     * @param int $start - the start date of infractions you want to find
     * @param int $limit - the total number of infractions you want to limit by. If 0 return all
     * @param bool $user_id - the id of the user you want to find infractions for (or false if all users)
     *
     * @return array of all users infractions if user_id is false, otherwise returns all infractions
     * that were given to user with id user_id.
     */
    public function get_users_infractions($start = 0, $limit = 20, $user_id = false) {

        $sql = 'SELECT * FROM ' . $this->tables['inf_users'];
        if ($user_id) {
            $sql .= " where user_id={$user_id}";
        }
        $sql .= " ORDER BY date_created DESC ";
        $sql .= ($limit ? " LIMIT {$start}, {$limit} " : '');

        $result = $this->db->sql_query($sql);

        $rows = array();
        while ($row = $this->db->sql_fetchrow($result)) {
            $rows[] = $row;
        }

        $this->db->sql_freeresult($result);
        return $rows;
    }

    /**
     * Grabs infractions issued based off moderator ID.
     * @param $mod_id           Moderator who issued infraction
     * @param int $limit        Number of infractions returned
     * @return array
     */

    public function get_infractions_by_moderator($mod_id, $limit=0) {
        $result = $this->db->sql_query('SELECT * FROM ' . $this->tables['inf_users'] .
            ' WHERE moderator_id=' . $mod_id . ' ORDER BY date_created DESC' . ($limit ? ' LIMIT ' . $limit : ''));

        $rows = array();
        while ($row = $this->db->sql_fetchrow($result)) {
            $rows[] = $row;
        }

        $this->db->sql_freeresult($result);
        return $rows;

    }

    /**
     * @param int $user_id - the id of the user you want to find infractions for
     * @param int $start - the start date of infractions you want to find
     * @param int $limit - the total number of infractions you want to limit by
     *
     * @return array
     */
    public function get_user_infractions($user_id, $start=0, $limit=20, $sort_status=false) {
        return $this->get_users_infractions($start, $limit, $user_id);
    }

    /**
     *
     * @param $infraction_id
     * @param $status
     *
     * @return bool
     */
    private function remove_user_infraction($infraction_id, $status) {
        if ($status != self::STATUS_CANCELLED && $status != self::STATUS_EXPIRED) {
            return false;
        }

        $user_inf = $this->get_user_infraction($infraction_id);

        $user_id = $user_inf['user_id'];
        $points  = $user_inf['points'];

        $sql = 'UPDATE ' .  $this->tables['inf_users'] . ' SET status='.$status.' WHERE id=' . $infraction_id . ' AND status='.self::STATUS_ACTIVE;

        if ($this->db->sql_query($sql) && $this->db->sql_affectedrows() > 0) {
            if ($points != self::IS_WARNING) {
                $new_points = $this->sub_user_points($user_id, $points);
                $this->rollback_user($user_id, $new_points);
            }
        }

        return $user_inf;
    }

    /**
     * @param $user_id
     * @param $points
     * @return bool
     */
    private function add_user_points($user_id, $points) {
        if (!$points) {
            return false;
        }

        $update = $this->db->sql_query('UPDATE ' . USERS_TABLE  . ' SET user_infraction_points=user_infraction_points+' . $points . ' WHERE user_id=' . $user_id);

        if ($update && $this->db->sql_affectedrows() > 0) {
            $result = $this->db->sql_query('SELECT user_infraction_points FROM ' . USERS_TABLE . ' WHERE user_id=' . $user_id);
            $new_points = $this->db->sql_fetchrow($result);
            return $new_points['user_infraction_points'];
        }
    }

    /**
     * @param $user_id
     * @param $points
     * @return bool
     */
    private function sub_user_points($user_id, $points) {
        return $this->add_user_points($user_id, $points * -1);
    }

    /**
     * @param $infraction_id
     * @return bool|mixed
     */
    private function get_user_infraction($infraction_id) {
        $result = $this->db->sql_query('SELECT user_id, points FROM ' . $this->tables['inf_users'] . ' WHERE id=' . $infraction_id);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!row) {
            return false;
        }

        return $row;

    }

    /**
     * @param $user_id
     */
    private function reset_user_points($user_id) {
        $sql_ary = array (
            'user_infraction_points'    =>  (int) 0,
        );
        $sql = $this->db->sql_query(
            'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) .
            ' WHERE user_id=' . $user_id);

        $this->rollback_user($user_id, 0);

        return $sql;

    }

    /**
     * @param $group_id
     * @return mixed
     */
    private function get_group_rank($group_id) {
        $sql = $this->db->sql_query('SELECT group_rank FROM ' . GROUPS_TABLE .
            ' WHERE GROUP_ID=' . $group_id);

        $result = $this->db->sql_fetchrow($sql);
        $this->db->sql_freeresult($sql);

        return $result['group_rank'];
    }

    /**
     * @param $moderator_id
     * @param $user_id
     * @param $post_id
     * @param $topic_id
     * @param $date_created
     * @param $title
     * @param $reason
     * @param $infraction_id
     * @param $points
     * @param $expires_days
     * @param $status
     * @return string
     */
    private function add_user_infraction($moderator_id, $user_id, $post_id, $topic_id, $date_created, $title, $reason, $infraction_id, $points, $expires_days) {
        $sql_ary = array(
            'moderator_id'      => (int) $moderator_id,
            'user_id'           => (int) $user_id,
            'post_id'           => (int) $post_id,
            'topic_id'          => (int) $topic_id,
            'date_created'      => $date_created,
            'title'             => $title,
            'reason'            => $reason,
            'infraction_id'     => (int) $infraction_id,
            'points'            => (int) $points,
            'expires_days'      => (int) $expires_days,
            'status'            => (int) self::STATUS_ACTIVE,
        );

        $this->db->sql_query(
            'INSERT INTO ' . $this->tables['inf_users'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary)
        );

        return $this->db->sql_nextid();
    }

    /**
     * @param $user_id
     * @return mixed
     */
    private function get_user($user_id) {
        $sql = $this->db->sql_query('SELECT user_id, group_id, user_infraction_points, user_rank FROM ' . USERS_TABLE .
            ' WHERE USER_ID=' . $user_id);

        $result = $this->db->sql_fetchrow($sql);
        $this->db->sql_freeresult($sql);

        return $result;
    }

    /**
     * @param $user_id
     * @param $old_rank
     * @param $rule
     *
     * @return string
     */
    private function add_user_rule($user_id, $old_rank, $rule) {
        $sql_ary = array(
            'user_id'             => (int) $user_id,
            'group_id'            => (int) $rule['group_id'],
            'rank'                => (int) $old_rank,
            'point_level'         => (int) $rule['point_level'],
        );

        $this->db->sql_query(
            'INSERT INTO ' . $this->tables['group_applied'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary)
        );

        return $this->db->sql_nextid();
    }


    /**
     * @param $user_id
     * @param $new_points_total
     */
    private function rollback_user($user_id, $new_points_total) {
        if (!function_exists('group_user_del')) {
                include __DIR__ . '/../../../../includes/functions_user.php';
        }

        // -- first remove all the groups
        $revert = $this->db->sql_query('SELECT group_id FROM ' . $this->tables['group_applied'] . ' WHERE user_id=' . $user_id . ' AND point_level>' .
            $new_points_total . ' ORDER BY point_level DESC');

        if (!$this->db->sql_affectedrows()) {
            return false;
        }

        while ($row = $this->db->sql_fetchrow($revert)) {
            group_user_del($row['group_id'], $user_id);
        }

        // -- revert to the user's rank
        $revert = $this->db->sql_query('SELECT rank FROM ' . $this->tables['group_applied'] . ' WHERE user_id=' . $user_id . ' AND point_level>' .
            $new_points_total . ' ORDER BY point_level ASC LIMIT 1');

        $this->edit_user_rank($user_id, $row['rank']);

        $this->db->sql_query('DELETE FROM ' . $this->tables['group_applied'] . ' WHERE user_id='.$user_id.' AND point_level>'.$new_points_total);

        $this->db->sql_freeresult($revert);

        return true;
    }

    /**
     * @param $user_id
     * @param $info_update
     *
     * @return string
     */
    private function edit_user_rank ($user_id, $new_rank) {
        $update = array (
            'user_rank'       =>      (int) $new_rank,
        );

        return $this->db->sql_query(
            'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $update) .
            ' WHERE user_id=' . $user_id
        );
    }

    /**
     * @return array of (infraction id, title, count)
     */
    public function get_infraction_id_counts() {
        $user_infractions = array();

        $sql = 'SELECT infraction_id, title, count(*) as cnt from ' . $this->tables['inf_users'] . ' GROUP BY infraction_id ORDER BY cnt DESC';
        $result = $this->db->sql_query($sql);

        while ($row = $this->db->sql_fetchrow($result)) {
            $user_infractions[] = $row;
        }
        $this->db->sql_freeresult($result);

        return  $user_infractions;
    }

}


