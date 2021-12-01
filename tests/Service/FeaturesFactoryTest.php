<?php

namespace Pastell\Tests\Service;

use Pastell\Service\OptionalFeatureFactory;
use Pastell\Service\OptionalFeatures\DisplayOptionalFeatureInTestPage;
use Pastell\Service\OptionalFeatures\TestingFeature;
use PastellTestCase;

class FeaturesFactoryTest extends PastellTestCase
{

    private $featureFactory;

    public function setUp()
    {
        parent::setUp();
        $this->featureFactory = new OptionalFeatureFactory($this->getObjectInstancier());
    }

    public function testWhenNotConfigured()
    {
        $this->assertFalse($this->featureFactory->isEnabled(TestingFeature::class));
    }

    public function testWhenEnable()
    {
        $this->featureFactory->enable(TestingFeature::class);
        $this->assertTrue($this->featureFactory->isEnabled(TestingFeature::class));
    }

    public function testWhenDisable()
    {
        $this->featureFactory->disable(TestingFeature::class);
        $this->assertFalse($this->featureFactory->isEnabled(TestingFeature::class));
    }

    public function testWhenEnableByDefault()
    {
        $this->assertTrue($this->featureFactory->isEnabled(DisplayOptionalFeatureInTestPage::class));
    }

    public function testWithNonExistingFeature()
    {
        $this->assertFalse($this->featureFactory->isEnabled("nonExistingFeature"));
    }

    public function testGetAll()
    {
        $this->featureFactory->enable(TestingFeature::class);
        $all = $this->featureFactory->getAllOptionalFeatures();
        $this->assertTrue($all[TestingFeature::class]['is_enable']);
        $this->assertFalse($all[TestingFeature::class]['is_enable_by_default']);
    }
}
