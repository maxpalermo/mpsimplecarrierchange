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
<style>
    .panel-body>.row
    {
        margin-bottom: 12px !important;
    }
</style>
<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-search"></i>&nbsp;{l s='Search parameters' mod='mpsimplecarrierchange'}
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3 text-right">
                <label>{l s='Select carriers' mod='mpsimplecarrierchange'}</label>
            </div>
            <div class="col-md-8">
                <select class="input select cho-mult" id="select_carriers" multiple data-placeholder="{l s='Select one or more values' mod='mpsimplecarrierchange'}">
                    {foreach $carriers as $carrier}
                        <option value="{$carrier.id_carrier}">{$carrier.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 text-right">
                <label>{l s='Select carrier to change' mod='mpsimplecarrierchange'}</label>
            </div>
            <div class="col-md-8">
                <select class="input select cho-sng" id="select_carrier_change" data-placeholder="{l s='Select a carrier' mod='mpsimplecarrierchange'}">
                    <option/>
                    {foreach $carriers as $carrier}
                        <option value="{$carrier.id_carrier}">{$carrier.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 text-right">
                <label>{l s='Select order state' mod='mpsimplecarrierchange'}</label>
            </div>
            <div class="col-md-8">
                <select class="input select cho-mult" id="select_order_state" multiple data-placeholder="{l s='Select one or more values' mod='mpsimplecarrierchange'}">
                    {foreach $order_states as $order_state}
                        <option value="{$order_state.id_order_state}">{$order_state.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 text-right">
                <label>{l s='Select payment module' mod='mpsimplecarrierchange'}</label>
            </div>
            <div class="col-md-8">
                <select class="input select cho-mult" id="select_payment_module" multiple data-placeholder="{l s='Select one or more values' mod='mpsimplecarrierchange'}">
                    {foreach $payments as $payment}
                        <option value="{$payment.id_module}-{$payment.name}">{$payment.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 text-right">
                <label>{l s='Select date start' mod='mpsimplecarrierchange'}</label>
            </div>
            <div class="col-md-8">
                <input type="text" id='date_start' value="" class="input date fixed-width-md text-center">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 text-right">
                <label>{l s='Select date end' mod='mpsimplecarrierchange'}</label>
            </div>
            <div class="col-md-8">
                <input type="text" id='date_end' value="" class="input date fixed-width-md text-center">
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <button type="button" class="btn btn-default pull-right" id='btn-find'>
            <i class="process-icon-preview"></i>{l s='Find' mod='mpsimplecarrierchange'}
        </button>
    </div>
</div>
<div class="panel" id="result-list" style="display: none;">

</div>
<script type="text/javascript">
    $(document).ready(function(){
        bind();
        $('#btn-find').on('click', function(event){
            event.preventDefault();
            var btn = $(this);
            var result = {
                'carriers' : $('#select_carriers').chosen().val(),
                'carrier_change': $('#select_carrier_change').chosen().val(),
                'order_states': $('#select_order_state').chosen().val(),
                'payments': $('#select_payment_module').chosen().val(),
                'date_start': $('#date_start').val(),
                'date_end': $('#date_end').val()
            };
            $(btn).find('i').removeClass('process-icon-preview').addClass('process-icon-loading');
            $.ajax({
                type: 'post',
                dataType: 'json',
                data: 
                {
                    ajax: true,
                    action: 'getOrderList',
                    params: result
                },
                success: function(response)
                {
                    $(btn).find('i').removeClass('process-icon-loading').addClass('process-icon-preview');
                    $('#result-list').html(response.html).fadeIn();
                    bind();
                },
                error: function(reponse)
                {
                    console.log(response);
                    $(btn).find('i').removeClass('process-icon-loading').addClass('process-icon-preview');
                }
            });
        });
    });
    function bind()
    {
        $('#chk-select-all').on('change', function(){
            console.log('check');
            var checked = $(this).is(':checked');
            $('#table-result tbody input[type="checkbox"]').attr('checked', checked);
        });
        $('.cho-mult').chosen(
            {
                no_results_text: '{l s='No result found.' mod='mpsimplecarrierchange'}'
            }
        );
        $('.cho-sng').chosen({
            allow_single_deselect: true
        });
        $('.date').datepicker({
            dateFormat: 'yy-mm-dd'
        });
    }
</script>