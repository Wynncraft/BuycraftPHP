<?php
/**
 * A Buycraft interface for PHP
 *
 * @author Chris Ireland <ireland63@gmail.com>
 * @license MIT <http://opensource.org/licenses/MIT>
 */
class buycraftAPI
{
    /**
     * Your Buycraft salt
     *
     * @var string
     */
    private $apiKey = '';

    /**
     * Url to the api
     *
     * @var string
     */
    private $apiUrl = 'http://api.buycraft.net/v4?';

    /**
     * The format the api call should be returned as
     *
     * @var string
     */
    private $apiFormat = 'string';

    /**
     * Create a new class instance
     *
     * @param $apiKey
     * @param string $apiFormat
     * @throws Exception
     */
    function __construct($apiKey, $apiFormat = 'string')
    {
        // Set class variables
        $this->apiKey = $apiKey;
        $this->apiFormat = $apiFormat;

        // Validation format option
        if ($this->apiFormat !== 'string' && $this->apiFormat !== 'object' && $this->apiFormat !== 'array' )
            throw new Exception('Invalid API return format');
    }

    /**
     * API command builder and executor
     *
     * @param $action
     * @param null $do
     * @param null $commands
     * @param null $limit
     * @return array|mixed|string
     * @throws Exception
     */
    protected function apiCommand($action, $do = null, $commands = null, $limit = null)
    {
        // Build the http query to the api
        $apiQuery = array(
            'secret' => $this->apiKey,
            'action' => $action,
            'do' => $do,
            'commands' => $commands,
            'limit' => $limit
        );
        $apiQuery = http_build_query($apiQuery);

        // Query the api and fetch the response
        $ch = curl_init($this->apiUrl . $apiQuery);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $apiQuery = curl_exec($ch);

        if($apiQuery === false || curl_getinfo($ch,CURLINFO_HTTP_CODE) !== 200) {
            curl_close($ch);
            throw new Exception('Buycraft API failed to respond correctly');
        }

        curl_close($ch);

        // Handle formatting
        if ($this->apiFormat === 'object') {
            $apiQuery = json_decode($apiQuery);

        } elseif ($this->apiFormat === 'array') {
            $apiQuery = json_decode($apiQuery, true);

        }

        return $apiQuery;
    }

    /**
     * Return a JSON document of all payments made to Buycraft
     *
     * @param null $limit
     * @return array|mixed|string
     * @throws Exception
     */
    public function getPayments($limit = null)
    {
        return $this->apiCommand('payments', null, null, $limit);
    }

    /**
     * Return a JSON document of all available store categories
     *
     * @return array|mixed|string
     * @throws Exception
     */
    public function getCategories()
    {
        return $this->apiCommand('categories');
    }

    /**
     * Return a JSON document of all available store packages
     *
     * @return array|mixed|string
     * @throws Exception
     */
    public function getPackages()
    {
        return $this->apiCommand('packages');
    }

    /**
     * Return a JSON document of pending players
     *
     * @param null $limit
     * @return array|mixed|string
     * @throws Exception
     */
    public function getPendingPlayers($limit = null)
    {
        return $this->apiCommand('pendingUsers', null, null, $limit);
    }

    /**
     * Return JSON document of pending commands
     *
     * @param null $limit
     * @return array|mixed|string
     * @throws Exception
     */
    public function getPendingCommands($limit = null) {
        return $this->apiCommand('commands', 'lookup', null, $limit);
    }

    /**
     * Remove commands from the queue
     *
     * @param $commandIDs
     * @return array|mixed|string
     * @throws Exception
     */
    public function removePendingCommands($commandIDs) {
        if(!is_array($commandIDs))
            throw new Exception('Command IDs must be defined as an array');

        return $this->apiCommand('commands', 'removeId', json_encode($commandIDs));
    }

}
