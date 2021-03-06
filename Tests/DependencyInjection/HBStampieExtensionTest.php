<?php

namespace HB\StampieBundle\Tests\DependencyInjection;

use HB\StampieBundle\DependencyInjection\HBStampieExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 */
class HBStampieExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->extension = new HBStampieExtension();
    }

    public function testExceptionWhenInvalidAdapterSpecified()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid adapter "DummyAdapter" specified');
        $this->extension->load(array(
            'hb_stampie' => array(
                'adapter' => 'DummyAdapter',
                'mailer' => 'DummyMailer',
                'server_token' => 'token',
            ),
        ), $this->createContainerBuilder());
    }

    public function testExceptionWhenInvalidMailerSpecified()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid mailer "DummyMailer" specified');
        $this->extension->load(array(
            'hb_stampie' => array(
                'adapter' => 'buzz',
                'mailer' => 'DummyMailer',
                'server_token' => 'token',
            ),
        ), $this->createContainerBuilder());
    }

    public function testHBStampieMailerDefinitionIsBuilt()
    {
        $builder = $this->createContainerBuilder();

        $this->extension->load(array(
            'hb_stampie' => array(
                'adapter' => 'buzz',
                'mailer' => 'postmark',
                'server_token' => 'token',
            ),
        ), $builder);

        $this->assertTrue($builder->hasAlias('hb_stampie.mailer'));
        $this->assertEquals('hb_stampie.mailer.real', (string) $builder->getAlias('hb_stampie.mailer'));

        $this->assertTrue($builder->hasDefinition('hb_stampie.mailer.real'));
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $builder->getDefinition('hb_stampie.mailer.real'));
        $this->assertEquals('hb_stampie.mailer.postmark', $builder->getDefinition('hb_stampie.mailer.real')->getParent());

        $this->assertEquals(array(
            $builder->getDefinition('hb_stampie.adapter.buzz'),
            'token',
        ), $builder->getDefinition('hb_stampie.mailer.real')->getArguments());
    }

    public function testAlias()
    {
        $this->assertEquals('hb_stampie', $this->extension->getAlias());
    }

    public function testExtraLogging()
    {
        $builder = $this->createContainerBuilder();

        $this->extension->load(array(
            'hb_stampie' => array(
                'adapter' => 'buzz',
                'mailer' => 'postmark',
                'server_token' => 'token',
                'extra' => array(
                    'logging' => true,
                ),
            ),
        ), $builder);

        $this->assertTrue($builder->hasDefinition('hb_stampie.listener.message_logger'));
        $this->assertTrue($builder->hasDefinition('hb_stampie.data_collector'));
    }

    protected function createContainerBuilder($kernelDebug = false)
    {
        return new ContainerBuilder(new ParameterBag(array(
            'kernel.debug' => $kernelDebug,
        )));
    }
}
