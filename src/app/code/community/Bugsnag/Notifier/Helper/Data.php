<?php
/**
 * It is not typical for a helper to extend an observer, but it is better than duplicating code, and I don't have time
 * to refactor code to centralized helper methods.
 *
 * Class Bugsnag_Notifier_Helper_Data
 */

class Bugsnag_Notifier_Helper_Data extends Bugsnag_Notifier_Model_Observer
{
    public function logException(Exception $e)
    {
        $this->initalizeBugsnag();
        return $this->client->notifyException($e);
    }

    protected function initalizeBugsnag()
    {
        if (file_exists(Mage::getBaseDir('lib') . '/bugsnag-php/Autoload.php')) {
            require_once(Mage::getBaseDir('lib') . '/bugsnag-php/Autoload.php');
        } else {
            error_log("Bugsnag Error: Couldn't activate Bugsnag Error Monitoring due to missing Bugsnag PHP library!");
            return;
        }

        $this->apiKey = Mage::getStoreConfig("dev/Bugsnag_Notifier/apiKey");
        $this->notifySeverities = Mage::getStoreConfig("dev/Bugsnag_Notifier/severites");
        $this->filterFields = Mage::getStoreConfig("dev/Bugsnag_Notifier/filterFields");

        // Activate the bugsnag client
        if (!empty($this->apiKey)) {
            $this->client = new Bugsnag_Client($this->apiKey);

            $this->client->setReleaseStage($this->releaseStage())
                ->setErrorReportingLevel($this->errorReportingLevel())
                ->setFilters($this->filterFields());



            $this->client->setNotifier(self::$NOTIFIER);

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->addUserTab();
            }
        }
    }
}
