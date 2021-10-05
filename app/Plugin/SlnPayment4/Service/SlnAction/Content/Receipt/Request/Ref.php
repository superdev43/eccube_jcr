<?php
namespace Plugin\SlnPayment4\Service\SlnAction\Content\Receipt\Request;

use Plugin\SlnPayment4\Service\SlnAction\Content\Basic;
use Plugin\SlnPayment4\Service\SlnContent\Basic as contentBasic;
use Plugin\SlnPayment4\Service\SlnContent\Receipt\RefProcess;

class Ref extends Basic
{
    /**
     * @var RefProcess
     */
    protected $content;
    
    public function getDataKey()
    {
        return array(
            'MerchantId' => '',
            'MerchantPass' => '',
            'TransactionDate' => '',
            'OperateId' => '',
            'MerchantFree1' => '',
            'MerchantFree2' => '',
            'MerchantFree3' => '',
            'ProcessId' => '',
            'ProcessPass' => '',
        );
    }
    
    public function getOperatePrefix()
    {
        return "2";
    }
    
    /**
     * (non-PHPdoc)
     * @see \Plugin\SlnPayment4\Service\SlnAction\Content\Credit\Basic::setContent()
     */
    public function setContent(contentBasic $content) {
        if($content instanceof RefProcess) {
            $this->content = $content;
        } else {
            throw new \Exception("content not Plugin\SlnPayment4\Service\SlnContent\Receipt\RefProcess");
        }
    }
    
    /**
     * (non-PHPdoc)
     * @var Plugin\SlnPayment4\Service\SlnContent\Receipt\RefProcess
     */
    public function getContent() {
        return $this->content;
    }
}