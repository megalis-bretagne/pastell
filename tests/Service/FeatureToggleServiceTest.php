<?php

namespace Pastell\Tests\Service;

use Pastell\Service\FeatureToggleService;
use Pastell\Service\FeatureToggle\DisplayFeatureToggleInTestPage;
use Pastell\Service\FeatureToggle\TestingFeature;
use PastellTestCase;

class FeatureToggleServiceTest extends PastellTestCase
{
    private $featureFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->featureFactory = new FeatureToggleService($this->getObjectInstancier());
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
        $this->assertTrue($this->featureFactory->isEnabled(DisplayFeatureToggleInTestPage::class));
    }

    public function testWithNonExistingFeature()
    {
        $this->assertFalse($this->featureFactory->isEnabled("nonExistingFeature"));
    }

    public function testGetAll()
    {
        $this->featureFactory->enable(TestingFeature::class);
        $all = $this->featureFactory->getAllOptionalFeatures();
        $this->assertTrue($all[TestingFeature::class]['is_enabled']);
        $this->assertFalse($all[TestingFeature::class]['is_enabled_by_default']);
    }
}
