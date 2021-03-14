<?php 
$order_data = $templateVar['user']->get_order();
$names = [
	'M1-H.png' => 'My Neighbour MangaDex',
	'M2-H.png' => 'Anniversary',
	'M3-H.png' => 'Vaporwave',
	'M4-H.png' => 'Stylized White',
	'M5-H.png' => 'Stylized Black',
	'M6-H.png' => 'Simple Logo',
	'M7-H.png' => 'Banana',
	'M2-V.png' => 'Anniversary',
	'M3-V.png' => 'Vaporwave',
	'M4-V.png' => 'Stylized White',
	'M5-V.png' => 'Stylized Black',
	'M6-V.png' => 'Simple Logo',
	'M7-V.png' => 'Banana'
];
if (validate_level($templateVar['user'], 'member')) :  ?>

    <div>

        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link <?= $order_data ? '' : 'active' ?>" href="#shop" aria-controls="shop" data-toggle="tab">Shop</a></li>
            <li class="nav-item"><a class="nav-link" href="#mousemats" aria-controls="mousemats" data-toggle="tab">Mousemats</a></li>
            <li class="nav-item ml-auto"><a class="nav-link <?= $order_data ? 'active' : '' ?>" href="#orders" aria-controls="orders" data-toggle="tab">My order</a></li>
			
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane <?= $order_data ? '' : 'active' ?> pt-3" id="shop">
                <p>Welcome to the MangaDex shop! Due to a lot of interest in this idea, we will be doing a trial run of selling MangaDex branded mousemats. If all goes well, we will probably do this as an ongoing thing.</p>
				<p>This is an alternative way for you to support us with our ever increasing server costs.</p>
                <h3>Instructions</h3>
				<p>Please read this very carefully!</p>
				<ol>
					<li>You can find the mousemat designs and specification in the "Mousemats" tab. There are no limits as to how many of each design you can order, but only 1 order per user can be accepted.</li>
					<li>Orders will be accepted until <strong>Sunday 23rd June</strong>.</li>
					<li>If you want to amend your order, cancel it in the "My order" tab and start again. You can do so until <strong>Monday 24th June</strong>.</li>
					<li>Orders will be confirmed and invoices sent out via email on <strong>Monday 24th June</strong>. Please ensure that you are contactable via the email on record for your account.</li>
					<li>Orders need to be completed, with confirmation of payment and postage information, by <strong>Thursday 27th June</strong>.</li>
					<li>Orders will be posted on <strong>Friday 28th June</strong>.</li>
					<li>Depending on where you are located, you should receive your order within 1-2 weeks.</li>
				</ol>
            </div>
			
			<div role="tabpanel" class="tab-pane pt-3" id="mousemats">
				<h3>Technical specifications</h3>
				<ul>
					<li>Materials: Textile top layer, with a 3mm rubber base</li>
					<li>Dimensions: Approximately 24cm x 20cm</li>
					<li>Printing: Dye sublimation</li>
					<li>Packaging: Each mousemat is individually wrapped</li>
				</ul>
				
				<h3>Price</h3>
				<ul>
					<li>10 USD (or equivalent) per mousemat</li>
					<li>Your order can be charged in USD, GBP, EUR or AUD to try and minimise conversion fees.</li>
					<li>Final postage costs will be confirmed with you before your order is processed.</li>
				</ul>
				
				<h3>Discount (excluding postage)</h3>
				<ul>
					<li>2 items: 5% off</li>
					<li>3 items: 10% off</li>
					<li>4 items: 15% off</li>
					<li>5 items: 20% off</li>
					<li>6 or more items: 25% off</li>
				</ul>
				
				<h3>Postage (estimated)</h3>
				<ul>
					<li>Posted by Airmail</li>
					<li>Depending on destination, postage will vary from 2-3 USD per mousemat. Postage is usually more economical per mousemat for larger orders.</li>
				</ul>
				
				<h3>Payment options</h3>
				<ul>
					<li>UK: Direct bank transfer (sort code and account number provided)</li>
					<li>US: Direct bank transfer (routing number and account number provided)</li>
					<li>EU: Direct bank transfer (Bank code and IBAN provided)</li>
					<li>AU: Direct bank transfer (BSB code and account number provided)</li>
					<li>Paypal also accepted from anywhere (but the above options would incur less fees!)</li>
				</ul>
				
				<form method="post" id="order_form">
					<h3>Designs (Horizontal)</h3>
					<div class="row">
						<?php 
						$mousemats = read_dir('images/shop/mousemats/horizontal');
						foreach ($mousemats as $mousemat) :
						?>
						<div class="col-lg-3 col-md-4 col-sm-6 p-2">
							<a title="<?= $names[$mousemat] ?>" href="/images/shop/mousemats/horizontal/<?= $mousemat ?>" data-lightbox="horizontal" data-title="<?= $mousemat ?>"><img src="/images/shop/mousemats/horizontal/<?= $mousemat ?>" width="100%"></img></a>
							<input type="number" class="mt-1 form-control" id="<?= $mousemat ?>" name="<?= $mousemat ?>" value="0" />
						</div>
						<?php endforeach; ?>
					</div>
					
					<h3>Designs (Vertical)</h3>
					<div class="row">
						<?php 
						$mousemats = read_dir('images/shop/mousemats/vertical');
						foreach ($mousemats as $mousemat) :
						?>
						<div class="col-lg-3 col-md-4 col-sm-6 p-2">
							<a title="<?= $names[$mousemat] ?>" href="/images/shop/mousemats/vertical/<?= $mousemat ?>" data-lightbox="vertical" data-title="<?= $mousemat ?>"><img src="/images/shop/mousemats/vertical/<?= $mousemat ?>" width="100%"></img></a>
							<input type="number" class="mt-1 form-control" id="<?= $mousemat ?>" name="<?= $mousemat ?>" value="0" />
						</div>
						<?php endforeach; ?>
					</div>
					<select class="form-control selectpicker" id="payment" name="payment" required data-title="Please select a payment method">
                        <option value="1">UK (GBP): Direct bank transfer</option>
                        <option value="2">US (USD): Direct bank transfer</option>
                        <option value="3">EU (EUR): Direct bank transfer</option>
                        <option value="4">AU (AUD): Direct bank transfer</option>
                        <option value="5">Paypal (GBP)</option>
                        <option value="6">Paypal (EUR)</option>
                        <option value="7">Paypal (USD)</option>
                    </select>
					<button type="submit" class="mt-2 btn btn-block btn-success" id="order_button" <?= $order_data ? "disabled title='You have an existing order.'" : '' ?>><?= display_fa_icon('shopping-cart') ?> Submit order</button>
				</form>
            </div>
			
			<div role="tabpanel" class="tab-pane <?= $order_data ? 'active' : '' ?> pt-3" id="orders">
				<?php 
				if ($order_data) : ?>
				<p><strong>Order ID:</strong> <?= $order_data['order_id'] ?></p>
				<p><strong>Order Status:</strong> <?= ORDER_STATUSES[$order_data['status']] ?></p>
				<p><strong>Payment method:</strong> <?= PAYMENT_METHODS[$order_data['payment']] ?></p>
				<p><strong>Shipping address:</strong> To be confirmed via email once the order is processed.</p>
				<button data-id="<?= $order_data['order_id'] ?>" class='btn btn-danger cancel_order_button'><?= display_fa_icon('trash', 'Cancel', 'fas') ?> Cancel</button>
				<p><strong>Order:</strong></p>
				<div class="row">
				<?php foreach (json_decode($order_data['items'], true) as $key => $value) : ?>
				
				<?php if ($value) { 
					$key = str_replace('_', '.', $key); ?>
					<div class="col-lg-3 col-md-4 col-sm-6 p-2">
						<a title="<?= $names[$key] ?>" href="/images/shop/mousemats/<?= $key ?>" data-lightbox="order" data-title="<?= $key ?>"><img src="/images/shop/mousemats/<?= $key ?>" width="100%"></img></a>
						<input disabled type="number" class="mt-1 form-control" id="<?= $key ?>" name="<?= $key ?>" value="<?= $value ?>" />
					</div>
				<?php } ?>
				
				<?php endforeach; ?>
				</div>
				<button data-id="<?= $order_data['order_id'] ?>" class='btn btn-danger cancel_order_button'><?= display_fa_icon('trash', 'Cancel', 'fas') ?> Cancel</button>
				<?php else : ?>
                <p>You have no orders.</p>
				<?php endif; ?>
            </div>
			
        </div>

    </div>

<?php else : ?>

    <p>Welcome to the MangaDex shop, where we may sell various MangaDex branded merchandise in the future.</p>

<?php endif; ?>