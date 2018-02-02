<?php
namespace Minds\Core\Blockchain\Wallets\OffChain;

use Cassandra;
use Cassandra\Varint;
use Cassandra\Timestamp;
use Minds\Core\Data\Cassandra\Client;
use Minds\Core\Data\Cassandra\Prepared\Custom;
use Minds\Core\Di\Di;
use Minds\Entities\User;

class Sums
{
    /** @var Client */
    private $db;

    /** @var User */
    private $user;

    public function __construct($db = null)
    {
        $this->db = $db ? $db : Di::_()->get('Database\Cassandra\Cql');
    }

    public function setTimestamp($ts)
    {
        $this->timestamp = $ts;
        return $this;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the nalance
     */
    public function getBalance()
    {
        $query = new Custom();

        if ($this->user) {
            $query->query("SELECT 
                SUM(amount) as balance 
                FROM blockchain_transactions
                WHERE user_guid = ?
                AND wallet_address = 'offchain'", 
                [
                    new Varint((int) $this->user->guid)
                ]);
        } else {
            //$query->query("SELECT SUM(amount) as balance from rewards");
        }

        try{
            $rows = $this->db->request($query);
        } catch(\Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
        
        return (double) $rows[0]['balance'];
    }

}