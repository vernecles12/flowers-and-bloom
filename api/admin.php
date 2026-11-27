<?php
declare(strict_types=1);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#f7f4ed">
<title>Studio dashboard | Flowers & Bloom</title>
<style>
:root{color-scheme:light;--ink:#263b31;--muted:#718075;--paper:#f7f5ef;--card:#fffefa;--line:#e8e7dc;--green:#315b45;--pale:#edf2e9;--gold:#b78b52}
*{box-sizing:border-box}body{margin:0;background:var(--paper);color:var(--ink);font:15px/1.5 Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
a{color:inherit}.top{background:#263b31;color:#fff;padding:17px max(22px,calc((100% - 1180px)/2));display:flex;align-items:center;justify-content:space-between;gap:20px}.brand{font-family:Georgia,serif;font-size:23px;letter-spacing:-.5px}.brand span{color:#d9c5a2}.top small{display:block;color:#c6d2c8;font:10px/1.4 ui-sans-serif,sans-serif;letter-spacing:2px}.top a{text-decoration:none;color:#e9e6d9;font-size:13px}
main{max-width:1180px;margin:42px auto;padding:0 22px}.eyebrow{color:var(--gold);font-size:11px;letter-spacing:2px;text-transform:uppercase;font-weight:700}.heading{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:26px}h1{font:clamp(34px,5vw,50px)/1.08 Georgia,serif;letter-spacing:-1.5px;margin:8px 0}h1 em{font-weight:400;color:#9a7b57}.sub{color:var(--muted);margin:8px 0 0}.btn{border:0;background:var(--green);color:white;border-radius:4px;padding:12px 17px;font-weight:650;cursor:pointer}.btn:disabled{opacity:.55;cursor:wait}.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin:25px 0}.stat,.panel{background:var(--card);border:1px solid var(--line);border-radius:8px}.stat{padding:20px}.stat span{display:block;color:var(--muted);font-size:12px}.stat strong{display:block;font:32px/1.2 Georgia,serif;margin-top:7px}.panel{overflow:hidden}.panel-head{padding:20px 22px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:15px}.panel-head h2{font:24px Georgia,serif;margin:0}.tools{display:flex;gap:10px;align-items:center}.tools input,.tools select{border:1px solid var(--line);background:#fff;border-radius:4px;padding:10px 12px;color:var(--ink);min-width:140px}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:800px}th,td{text-align:left;padding:15px 18px;border-bottom:1px solid var(--line);vertical-align:middle}th{font-size:10px;letter-spacing:1.3px;text-transform:uppercase;color:var(--muted);font-weight:700;background:#fbfaf6}td{font-size:13px}td strong{font-size:14px}.muted{color:var(--muted)}.money{font-weight:700;white-space:nowrap}.status{display:inline-block;border-radius:30px;background:var(--pale);color:var(--green);padding:5px 9px;font-size:11px;white-space:nowrap}.status.cancel{background:#f8e9e6;color:#9b4037}.status.wait{background:#f6f0df;color:#8a682e}.status-select{padding:8px;border:1px solid var(--line);background:white;border-radius:4px;color:var(--ink);max-width:175px}.empty,.message{padding:35px;text-align:center;color:var(--muted)}.message{margin:16px 0;border-radius:6px;background:#fff}.message.error{color:#a43e35;background:#fff0ed}.foot{color:var(--muted);font-size:12px;margin:17px 0 0}.hidden{display:none!important}
@media(max-width:760px){main{margin:28px auto}.heading{align-items:start;flex-direction:column}.stats{grid-template-columns:repeat(2,1fr)}.panel-head{align-items:start;flex-direction:column}.tools{width:100%;flex-wrap:wrap}.tools input{flex:1;min-width:100%}.tools select{flex:1}.top{padding:15px 20px}}
</style>
</head>
<body>
<header class="top"><div class="brand">flowers <span>&</span> bloom<small>STUDIO MANAGEMENT</small></div><a href="/">← Back to storefront</a></header>
<main>
<div class="heading"><div><div class="eyebrow">A little view behind the flower table</div><h1>Studio <em>dashboard.</em></h1><p class="sub" id="welcome">Loading your shop overview…</p></div><button class="btn" id="refresh">Refresh overview ↻</button></div>
<div id="notice" class="message hidden" role="status"></div>
<section class="stats" aria-label="Shop overview">
<div class="stat"><span>Total orders</span><strong id="orders-count">—</strong></div>
<div class="stat"><span>Customers</span><strong id="customers-count">—</strong></div>
<div class="stat"><span>Order value*</span><strong id="revenue">—</strong></div>
<div class="stat"><span>Orders to action</span><strong id="open-count">—</strong></div>
</section>
<section class="panel"><div class="panel-head"><div><div class="eyebrow">Customer activity</div><h2>Recent orders</h2></div><div class="tools"><input id="search" type="search" placeholder="Search order or customer…" aria-label="Search orders"><select id="status-filter" aria-label="Filter orders"><option value="all">All statuses</option><option>Received</option><option>Preparing</option><option>Ready for dispatch</option><option>Out for delivery</option><option>Delivered</option><option>Cancelled</option></select></div></div>
<div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Delivery to</th><th>Placed</th><th>Total</th><th>Status</th><th>Update</th></tr></thead><tbody id="orders-body"><tr><td colspan="7" class="empty">Loading orders…</td></tr></tbody></table></div></section>
<p class="foot">*Order value is the total of non-cancelled orders, not verified collected revenue. Payment is handled manually by the shop. Customer addresses and phone numbers are visible only to authorized studio administrators.</p>
</main>
<script>
(function(){
'use strict';
var csrf='',orders=[];
var statuses=['Received','Preparing','Ready for dispatch','Out for delivery','Delivered','Cancelled'];
var $=function(id){return document.getElementById(id)};
var money=new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP',maximumFractionDigits:0});
function escapeHtml(v){return String(v==null?'':v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]})}
function notice(message,error){$('notice').textContent=message;$('notice').classList.remove('hidden','error');if(error)$('notice').classList.add('error')}
async function request(action,data){var options={method:data?'POST':'GET',credentials:'same-origin',headers:{Accept:'application/json'}};if(data){options.headers['Content-Type']='application/json';options.body=JSON.stringify(Object.assign({csrf:csrf},data))}var response=await fetch('/api/shop.php?action='+encodeURIComponent(action),options);var result=await response.json();if(!response.ok||result.ok===false){var e=new Error(result.message||'The request could not be completed.');e.status=response.status;throw e}return result}
function badge(status){var cls=status==='Cancelled'?'cancel':(['Received','Awaiting manual payment'].indexOf(status)>=0?'wait':'');return '<span class="status '+cls+'">'+escapeHtml(status||'Received')+'</span>'}
function render(){var query=$('search').value.trim().toLowerCase(),filter=$('status-filter').value;var filtered=orders.filter(function(o){var hay=[o.id,o.customer&&o.customer.name,o.customer&&o.customer.email,o.recipient,o.address].join(' ').toLowerCase();return (!query||hay.indexOf(query)>=0)&&(filter==='all'||o.status===filter)});$('orders-body').innerHTML=filtered.length?filtered.map(function(o){var date=o.createdAt?new Date(o.createdAt).toLocaleString('en-PH',{dateStyle:'medium',timeStyle:'short'}):'—';return '<tr><td><strong>'+escapeHtml(o.id)+'</strong><br><span class="muted">'+escapeHtml(o.paymentMethod||'')+'</span></td><td><strong>'+escapeHtml(o.customer&&o.customer.name||'Customer')+'</strong><br><span class="muted">'+escapeHtml(o.customer&&o.customer.email||'')+'</span></td><td>'+escapeHtml(o.recipient||'')+'<br><span class="muted">'+escapeHtml(o.address||'')+'</span><br><span class="muted">'+escapeHtml(o.phone||'')+'</span></td><td>'+escapeHtml(date)+'</td><td class="money">'+money.format(Number(o.total)||0)+'</td><td>'+badge(o.status)+'</td><td><select class="status-select" data-order="'+escapeHtml(o.id)+'" aria-label="Update status for '+escapeHtml(o.id)+'">'+statuses.map(function(s){return '<option '+(s===o.status?'selected':'')+'>'+s+'</option>'}).join('')+'</select></td></tr>'}).join(''):'<tr><td colspan="7" class="empty">No orders match this search.</td></tr>'}
async function load(){ $('refresh').disabled=true;$('notice').classList.add('hidden');try{var state=await request('state');csrf=state.csrf;var data=await request('admin_overview');orders=data.orders||[];$('welcome').textContent='Signed in as '+data.admin.name+' · '+data.admin.email;$('orders-count').textContent=data.stats.orders;$('customers-count').textContent=data.stats.customers;$('revenue').textContent=money.format(data.stats.revenue);$('open-count').textContent=data.stats.openOrders;render()}catch(e){$('orders-body').innerHTML='<tr><td colspan="7" class="empty">The dashboard could not be loaded.</td></tr>';$('welcome').textContent='Administrator access is required.';notice(e.message,true)}finally{$('refresh').disabled=false}}
$('refresh').addEventListener('click',load);$('search').addEventListener('input',render);$('status-filter').addEventListener('change',render);
$('orders-body').addEventListener('change',async function(event){var select=event.target.closest('select[data-order]');if(!select)return;var old=orders.find(function(o){return o.id===select.dataset.order});if(!old)return;var next=select.value;select.disabled=true;try{await request('admin_update_order',{orderId:old.id,status:next});old.status=next;notice('Order '+old.id+' updated to '+next+'.');render()}catch(e){notice(e.message,true);select.value=old.status}finally{select.disabled=false}});
load();
})();
</script>
</body></html>