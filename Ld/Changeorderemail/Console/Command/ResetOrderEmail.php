<?php

namespace Ld\Changeorderemail\Console\Command;

use Magento\Sales\Model\Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class ResetOrderEmail
 * @package Ld\Changeorderemail\Console\Command
 */
class ResetOrderEmail extends Command
{
    const ORDER_ID_OR_EMAIL_ARGUMENT = "orderIdOrEmail";
    public $_orderFactory;

    /**
     * ResetOrderEmail constructor used to inject the order collection factory .
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory
     */
    public function __construct(\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory)
    {
        $this->_orderFactory = $orderFactory;
        parent::__construct();
    }

    /**
     * method to create the order Collection
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrderCollection()
    {
        return $this->_orderFactory->create();
    }

    /**
     * default execute method from command interface
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $order_id_or_email = $input->getArgument(self::ORDER_ID_OR_EMAIL_ARGUMENT);

        if (filter_var($order_id_or_email, FILTER_VALIDATE_EMAIL)) {
            $this->findAndChangeBy($input, $output, Order::CUSTOMER_EMAIL, $order_id_or_email);
        } elseif ($order_id_or_email) {
            $this->findAndChangeBy($input, $output, Order::INCREMENT_ID, $order_id_or_email);
        } else {
            $output->writeln("Not valid order_id or email");
        }
    }

    /**
     * method to find and change all order that match the attribute filters
     * @param $input
     * @param $output
     * @param $attribute_type
     * @param $attribute_value
     * @return void
     */
    protected function findAndChangeBy($input, $output, $attribute_type, $attribute_value)
    {
        $changedOrders = [];
        $orderCollection = $this->getOrderCollection();
        $orderCollection->addFilter($attribute_type, $attribute_value);
        if ($orderCollection->getTotalCount() !== 0) {
            $email = $orderCollection->getFirstItem()->getCustomerEmail();
            $output->writeln($email);
            $changedOrders = $this->changeEmailOrder($email, $this->askForNewEmail($input, $output));
        }
        if (count($changedOrders)) {
            $output->writeln("Order changed are :" . str_replace("array", "", var_export($changedOrders, true)));
        } else {
            $output->writeln("No Order changed matching the input ");
        }
    }

    /**
     * method to ask for a valid email input
     * @param $input
     * @param $output
     * @return mixed
     */
    protected function askForNewEmail($input, $output)
    {
        $i = 0;
        do {
            $helper = $this->getHelper('question');
            $question = new Question('please enter a' . ($i != 0 ? " valid email: " : "n email: "), '');
            $email_response = $helper->ask($input, $output, $question);
            $i++;
        } while (!filter_var($email_response, FILTER_VALIDATE_EMAIL));

        return $email_response;
    }

    /**
     * change all orders that match the $oldEmail to a $newEmail
     * @param $oldEmail
     * @param $newEmail
     * @return array
     */
    protected function changeEmailOrder($oldEmail, $newEmail)
    {
        $orderCollection = $this->getOrderCollection();
        $orderCollection->addFilter(Order::CUSTOMER_EMAIL, $oldEmail);
        $orderChanged = [];
        foreach ($orderCollection as $order_found) {
            $order_found->setData(Order::CUSTOMER_EMAIL, $newEmail);
            $orderChanged[] = $order_found->getData(Order::INCREMENT_ID);
            $order_found->save();
        }
        return $orderChanged;
    }

    /**
     * default configure command method
     */
    protected function configure()
    {
        $this->setName("ld_changeorderemail:resetorderemail");
        $this->setDescription("reset the order email. an order id or email is required");
        $this->setDefinition([
            new InputArgument(self::ORDER_ID_OR_EMAIL_ARGUMENT, InputArgument::OPTIONAL, "ORDER ID OR EMAIL"),
        ]);
        parent::configure();
    }
}
