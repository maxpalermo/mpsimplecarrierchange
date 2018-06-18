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
    .table-result thead tr th
    {
        font-weight: bold !important;
        text-align: center;
    }
    .table-result td
    {
        white-space: nowrap !important;
        padding-bottom: 8px !important;
    }
    .panel-foot
    {
        height: 64px;
        border-bottom: 1px solid #777788;
        overflow: hidden;
        border-color: #eee;
        background-color: #FCFDFE;
        margin-bottom: 15px;
        padding-bottom: 12px;
        width: 100%;
    }
</style>
<div class="container">
    <div class="row">
        <div class="col-md-2 panel-foot text-center">
            <ul class="pagination">
                <li>
                    <span class="badge">{l s='Total' mod='mpsimplecarrierchange'} {$total}</span>
                </li>
            </ul>
        </div>
        <div class="col-md-4 panel-foot text-center">    
            <ul class="pagination">
                <li>
                    <a href="javascript:void(0);" class="pagination-link" data-page="1" data-list-id="log">
                        <i class="icon-double-angle-left"></i>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="pagination-link" data-page="{$i}" data-list-id="log">
                        <i class="icon-angle-left"></i>
                    </a>
                </li>
                {for $i = 0 to 9}
                    <li>
                        <a href="javascript:void(0);" class="pagination-link" data-page="{$i}" data-list-id="log">
                            {$i+1}
                        </a>
                    </li>
                {/for}
                <li>
                    <a href="javascript:void(0);" class="pagination-link" data-page="{$i+1}" data-list-id="log">
                        <i class="icon-angle-right"></i>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="pagination-link" data-page="{$pages}" data-list-id="log">
                        <i class="icon-double-angle-right"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-md-6 panel-foot text-center">
            <ul class="pagination">
                <li class="pagination-link" style="margin-right: 12px;">
                    <span class="badge">
                        {l s='Page' mod='mpsimplecarrierchange'}
                        &nbsp;
                        {$current_page}
                        &nbsp;
                        {l s='of' mod='mpsimplecarrierchange'}
                        &nbsp;
                        {$pages}
                    </span>
                </li>
                <li style="display: inline-block;">
                    <select class="input select" style="display: inline-block;">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="500">500</option>
                        <option value="1000">1000</option>
                    </select>
                </li>
                <li>
                    <strong>{l s='results for page'}</strong>
                </li>
            </ul>
        </div>
    </div>
    <br><br>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-hover table-result" id="table-result">
                <thead>
                    <tr>
                        <th style="text-align: left;">
                            <br>
                            <input type="checkbox" id="chk-select-all" checked> 
                        </th>
                        <th>
                            {l s='Reference' mod='mpsimplecarrierchange'}
                            <br>
                            <input type="text" class="input" value="">
                        </th>
                        <th>
                            {l s='Date' mod='mpsimplecarrierchange'}
                            <br>
                            <input type="text" class="input date" value="">
                            <br>
                            <input type="text" class="input date" value="">
                        </th>
                        <th>
                            {l s='Carrier' mod='mpsimplecarrierchange'}
                            <br>
                            <input type="text" class="input datepicker" value="">
                        </th>
                        <th>
                            {l s='Payment' mod='mpsimplecarrierchange'}
                            <br>
                            <input type="text" class="input datepicker" value="">
                        </th>
                        <th>
                            {l s='Total' mod='mpsimplecarrierchange'}
                            <br>
                            --
                        </th>
                        <th>
                            {l s='Customer' mod='mpsimplecarrierchange'}
                            <br>
                            <input type="text" class="input datepicker" value="">
                            <br>
                            <button type="button" class="btn btn-warning pull-right" id="btn-reset">
                                <i class="icon icon-refresh"></i>&nbsp;{l s='Reset' mod='mpsimplecarrierchange'}
                            </button>
                            <button type="button" class="btn btn-info pull-right" id="btn-filter" style="margin-right: 12px;">
                                <i class="icon icon-find"></i>&nbsp;{l s='Filter' mod='mpsimplecarrierchange'}
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $result as $order}
                        <tr class="{cycle values='odd,even'}">
                            <td><input type="checkbox" checked></td>
                            <td>{$order.reference}</td>
                            <td>{$order.date_add|date_format}</td>
                            <td>{$order.carrier}</td>
                            <td>{$order.payment}</td>
                            <td>{displayPrice price=$order.total_paid}</td>
                            <td>
                                {if !empty($order.company)}
                                    {$order.company}
                                {else}
                                    {$order.firstname} {$order.lastname}
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>