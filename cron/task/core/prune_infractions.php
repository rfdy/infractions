<?php
/**
 *
 * @package Auto Backup
 * @copyright (c) 2013 Pico88
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rfd\infractions\cron\task\core;

/**
 * @ignore
 */
if (!defined('IN_PHPBB')) {
    exit;
}

class prune_infractions extends \phpbb\cron\task\base
{
    /** @var  \rfd\infractions\Service\UserInfractionsManager */
    protected $manager;
    protected $request;
    protected $config;
    protected $db;

    /**
     * Constructor.
     *
     * @param string $phpbb_root_path The root path
     * @param string $php_ext The PHP extension
     * @param phpbb_config $config The config
     * @param phpbb_db_driver $db The db connection
     */
    public function __construct(\rfd\infractions\Service\UserInfractionsManager $manager,\phpbb\request\request $request, \phpbb\config\config $config, \phpbb\db\driver\driver $db)
    {
        $this->manager = $manager;
        $this->request = $request;
        $this->config = $config;
        $this->db = $db;
    }

    /**
     * Runs this cron task.
     *
     * @return null
     */
    public function run() {
        $this->config->set('auto_prune_start', strtotime('tomorrow 1am'));
       // $this->config->set('auto_prune_start', $this->request->server('REQUEST_TIME'));
        $infractions = $this->manager->get_expired_infractions($this->request->server('REQUEST_TIME'));
        foreach ($infractions as $inf) {
            $this->manager->expire_user_infraction($inf['id']);
        }
        add_log('admin', 'EXPIRE_INFRACTIONS');
    }

    /**
     * Returns whether this cron task can run, given current board configuration.
     *
     * @return bool
     */
    public function is_runnable()
    {
        return true;
    }

    /**
     * Returns whether this cron task should run now, because enough time
     * has passed since it was last run.
     *
     * @return bool
     */
    public function should_run()
    {
        if (!$this->config['auto_prune_start']) {
            return true;
        }
        else {
            $runtime = $this->config['auto_prune_start'];
            if ($this->request->server('REQUEST_TIME') >= $runtime) {
                return true;
            }
            else return false;
        }
    }
}
