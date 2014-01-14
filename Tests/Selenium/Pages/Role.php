<?php

namespace Oro\Bundle\UserBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

class Role extends AbstractPageEntity
{
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $name;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $label;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $accessLevel;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $owner;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
        $this->label = $this->test->byId('oro_user_role_form_label');
        $this->owner = $this->test->select($this->test->byId('oro_user_role_form_owner'));
    }

    public function setLabel($label)
    {
        $this->label->value($label);
        return $this;
    }

    public function getLabel()
    {
        return $this->label->value();
    }

    public function setOwner($owner)
    {
        $this->owner->selectOptionByLabel($owner);

        return $this;
    }

    public function getOwner()
    {
        return trim($this->owner->selectedLabel());
    }

    /**
     * @param $entityName string of ACL resource name
     * @param $aclAction array of actions such as create, edit, delete, view, assign
     * @param $accessLevel
     *
     * @return $this
     */
    public function setEntity($entityName, $aclAction, $accessLevel)
    {
        foreach ($aclAction as $action) {
            $action = strtoupper($action);
            $xpath = $this->test->byXpath(
                "//div[strong/text() = '{$entityName}']/ancestor::tr//input" .
                "[contains(@name, '[$action][accessLevel')]/preceding-sibling::a"
            );
            $this->test->moveto($xpath);
            $xpath->click();
            $this->waitForAjax();
            $this->accessLevel = $this->test->select(
                $this->test->byXpath(
                    "//div[strong/text() = '{$entityName}']/ancestor::tr//select" .
                    "[contains(@name, '[$action][accessLevel')]"
                )
            );
            $this->accessLevel->selectOptionByLabel($accessLevel);
        }

        return $this;
    }

    /**
     * @param $capabilityName array of Capability ACL resources
     * @param $accessLevel
     *
     * @return $this
     */
    public function setCapability($capabilityName, $accessLevel)
    {
        foreach ($capabilityName as $name) {
            $xpath = $this->test->byXpath(
                "//div[strong/text() = '{$name}']/following-sibling::div//a"
            );
            $this->test->moveto($xpath);
            $xpath->click();
            $this->waitForAjax();
            $this->accessLevel = $this->test->select(
                $this->test->byXpath("//div[strong/text() = '{$name}']/following-sibling::div//select")
            );
            $this->accessLevel->selectOptionByLabel($accessLevel);
        }

        return $this;
    }
}
