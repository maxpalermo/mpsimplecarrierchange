{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author mpsoft, Massimiliano Palermo <info@mpsoft.it>
*  @copyright  2017-2020 mpsoft®
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Massimiliano Palermo, mpsoft®
*}
<div id="row-change-carrier">
    <div class="row">
        <div class="col-md-12">
            <hr>
        </div>
    </div>
    <div class="row" style="margin-bottom: 12px;">
        <div class="col-md-12">
            <i class="icon icon-truck"></i>&nbsp;<span><strong>{l s='Change carrier' mod='mpsimplecarrierchange'}</strong></span>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9">
            <select id="selChangeCarrier">
                <option value="0">{l s='Select a carrier' mod='mpsimplecarrierchange'}</option>
                {foreach $carriers as $carrier}
                    <option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="col-md-3">
            <button 
                class="btn btn-info pull-right" 
                id="submit_change_carrier">
                <i class="icon icon-refresh"></i> {l s='Change' mod='mpsimplecarrierchange'}
            </button>
        </div>
    </div>     
</div>

<script type="text/javascript">
    $(document).ready(function()
    {
        $('#selChangeCarrier').chosen({
            width: '100%'
        });
        $("#row-change-carrier").detach().appendTo($("#shipping"));
        $('#submit_change_carrier').on('click', function(){

            if ($('#selChangeCarrier').val() == 0) {
                $.growl.error({
                    title: '{l s='Error' mod='mpsimplecarrierchange'}',
                    message: '{l s='Please select a carrier first.' mod='mpsimplecarrierchange'}' 
                });
                return false();
            }

            $.ajax({
                type: 'POST',
                data: 
                {
                    ajax: true,
                    action: 'changeCarrier',
                    token: '{$token|escape:'htmlall':'UTF-8'}',
                    id_order: '{$id_order|escape:'htmlall':'UTF-8'}',
                    id_carrier: $('#selChangeCarrier').val(),
                }, 
                success: function(response)
                {
                    var split = String(response).split('----SPLIT----');
                    
                    if (split.length) {
                        var data = JSON.parse(split[1]);
                    } else {
                        $.growl.error({
                            title: '{l s='Error' mod='mpsimplecarrierchange'}',
                            message: '{l s='Ajax result error.' mod='mpsimplecarrierchange'}' 
                        });
                        return false();
                    }

                    if (data.result) {
                        $.growl.notice({
                            title: '{l s='Operation done' mod='mpsimplecarrierchange'}',
                            message: '{l s='Carrier changed, wait for page refresh.' mod='mpsimplecarrierchange'}'
                        });
                        setTimeout(function(){ location.reload(); }, 1500);
                    } else {
                        $.growl.error({
                           title: '{l s='Error' mod='mpsimplecarrierchange'}',
                           message: '{l s='Unable to change carrier.' mod='mpsimplecarrierchange'}' 
                        });
                    }
                },
                error: function(response){
                    console.log(response);
                }
            })
        });
    });
</script>
