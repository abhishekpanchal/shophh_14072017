<?php 

//require_once 'Customweb/Payment/Util.php';
//require_once 'Customweb/Beanstream/Container.php';


/**
 *
 * @author Thomas Brenner
 * 
 *
 */
class Customweb_Beanstream_AbstractParameterBuilder {
	
		private $configuration;
		private $transaction;
		private $container;
	
		public function __construct(Customweb_Beanstream_Authorization_Transaction $transaction, Customweb_DependencyInjection_IContainer $container) {
			$this->container = new Customweb_Beanstream_Container($container);
			$this->configuration = $this->getContainer()->getBean('Customweb_Beanstream_Configuration');
			$this->transaction = $transaction;
		}

		/**
		 * @return Customweb_Beanstream_Configuration
		 */
		protected function getConfiguration(){
			return $this->configuration;
		}
		
		protected function getTransaction() {
			return $this->transaction;
		}
		
		public function getContainer(){
			return $this->container;
		}
		
		protected function getOrderContext() {
			return $this->transaction->getTransactionContext()->getOrderContext();
		}
		
		protected function getTransactionContext() {
			return $this->transaction->getTransactionContext();	
		}
		
		protected final function getTransactionAppliedSchema(Customweb_Payment_Authorization_ITransaction $transaction)
		{
			$schema = $this->getConfiguration()->getOrderIdSchema();
			$id = $transaction->getTransactionContext()->getTransactionId();
		
			return preg_replace("/[^A-Za-z0-9]/", '', Customweb_Payment_Util::applyOrderSchema($schema, $id, 64));
		}
			
}