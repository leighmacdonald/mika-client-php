<?php declare(strict_types=1);

namespace Mika;

class TrackerConfig
{
    public const ANN_INTERVAL = "tracker_announce_interval";
    public const ANN_INTERVAL_MIN = "tracker_announce_interval_min";
    public const REAPER_INTERVAL = "tracker_reaper_interval";
    public const BATCH_INTERVAL = "tracker_batch_update_interval";
    public const MAX_PEERS = "tracker_max_peers";
    public const AUTO_REGISTER = "tracker_auto_register";
    public const ALLOW_ROUTABLE = "tracker_allow_non_routable";
    public const GEODB_ENABLED = "geodb_enabled";

    private array $update_state = [
        'update_keys' => []
    ];

    /**
     * @param string|integer|bool $key
     * @param $value
     */
    public function set(string $key, $value)
    {
        if (!in_array($key, $this->update_state, true)) {
            array_push($this->update_state['update_keys'], $key);
        }
        $this->update_state[$key] = $value;
    }

    public function json()
    {
        return $this->update_state;
    }
}