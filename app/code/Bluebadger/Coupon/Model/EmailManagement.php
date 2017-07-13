<?php
namespace Bluebadger\Coupon\Model;

/**
 * Class EmailManagement
 * @package Bluebadger\Coupon\Model
 */
class EmailManagement
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * EmailManagement constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->resource = $resource;
    }

    /**
     * Check if the email is in the table.
     * @param string $email
     */
    public function isEmailValid(string $email)
    {
        $isEmailValid = false;

        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from(['t1' => $connection->getTableName('bluebadger_coupon_email')])
            ->where("t1.email=?", $email);
        $data = $connection->fetchAll($select);

        if (!empty($data)) {
            $isEmailValid = true;
        }

        return $isEmailValid;
    }

    /**
     * @return mixed
     */
    private function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->resource->getConnection('core_write');
        }
        return $this->connection;
    }
}