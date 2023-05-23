<?php

namespace Sulytics\Saml2\Commands;

use Sulytics\Saml2\Models\ServiceProvider;
use Illuminate\Console\Command;

class CreateServiceProvider extends Command
{

    /**
     * The name and signature of the console command.
     * Sample default: php artisan saml2:createServiceProvider --x509cert=file:///Users/majafritschi/Sites/certs/sulyticstool.crt --privateKey=file:///Users/majafritschi/Sites/certs/sulyticstool.key
     *
     * @var string
     */
    protected $signature = 'saml2:createServiceProvider
                            { --nameIdFormat= : SP Name ID Format ("email address" by default) }
                            { --x509cert= : x509 certificate (base64) }
                            { --x509certNew= : new x509 certificate (base64) }
                            { --privateKey= : SP private key or file url }
                            { --entityId= : SP entity ID }
                            { --ascUrl= : SP assertion consumer URL }
                            { --ascBinding= : SP assertion consumer binding ("" by default) }
                            { --sloUrl= : SP single logout url }
                            { --sloBinding= : SP single logout binding ("HTTP-POST" by default) }';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (!$x509cert = $this->option('x509cert')) {
            $this->error('x509 certificate (base64) must be passed as an option --x509cert');

            return;
        }

        if (!$privateKey = $this->option('privateKey')) {
            $this->error('Private Key must be passed as an option --privateKey');

            return;
        }

        $serviceProviderAttributes = [
            'sp_x509_cert' => $x509cert,
            'sp_private_key' => $privateKey,
        ];

        if(!empty($this->option('entityId'))) {
            $serviceProviderAttributes['sp_entity_id'] = $this->option('entityId');
        }
        if(!empty($this->option('nameIdFormat'))) {
            $serviceProviderAttributes['sp_name_id_format'] = $this->option('nameIdFormat');
        }
        if(!empty($this->option('x509certNew'))) {
            $serviceProviderAttributes['sp_x509_cert_new'] = $this->option('x509certNew');
        }
        if(!empty($this->option('ascUrl'))) {
            $serviceProviderAttributes['sp_assertion_consumer_url'] = $this->option('ascUrl');
        }
        if(!empty($this->option('ascBinding'))) {
            $serviceProviderAttributes['sp_assertion_consumer_binding'] = $this->option('ascBinding');
        }
        if(!empty($this->option('sloUrl'))) {
            $serviceProviderAttributes['sp_single_logout_service_url'] = $this->option('sloUrl');
        }
        if(!empty($this->option('sloBinding'))) {
            $serviceProviderAttributes['sp_single_logout_service_binding'] = $this->option('sloBinding');
        }

        $serviceProvider = new ServiceProvider($serviceProviderAttributes);

        if(!$serviceProvider->save()) {
            $this->error('Service Provider could not be created.');
            return;
        }

        $this->info("The service provider #{$serviceProvider->id} was successfully created.");
        $this->output->newLine();
    }
}