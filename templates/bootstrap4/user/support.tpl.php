<?php if (validate_level($templateVar['user'], 'member')) :  ?>

    <div>

        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" href="#home" aria-controls="home" data-toggle="tab">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="#history" aria-controls="history" data-toggle="tab">History</a></li>
			<!--
            <li class="nav-item"><a class="nav-link" href="#usd" aria-controls="usd" data-toggle="tab"><?= display_fa_icon('dollar-sign', 'USD') ?> <span class="d-none d-sm-inline">USD</span></a></li>
            <li class="nav-item"><a class="nav-link" href="#gbp" aria-controls="gbp" data-toggle="tab"><?= display_fa_icon('pound-sign', 'GBP') ?> <span class="d-none d-sm-inline">GBP</span></a></li>
            <li class="nav-item"><a class="nav-link" href="#eur" aria-controls="eur" data-toggle="tab"><?= display_fa_icon('euro-sign', 'EUR') ?> <span class="d-none d-sm-inline">EUR</span></a></li>
			-->
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active pt-3" id="home">
				
                <p>MangaDex is still growing steadily, approaching 12 million users per month and increasing. Due to COVID-19 and the closure of a certain popular site, this growth has been higher than expected and we have had to make unexpected server upgrades as a result.</p>				
				
				<h3>Our infrastructure</h3>
                <ul>
                    <li>1 webserver - $70</li>
                    <li>1 master database server - $70</li>
                    <li>2 slave database/image archive servers - $95 each</li>
                    <li>1 slave database server - covered by rdn</li>
                    <li>1 NA cache/image archive server - $140</li>
                    <li>1 EU cache server - $110</li>
                    <li>CDN for recent uploads/DDoS Protection - $180</li>
                    <li>Reverse proxy - covered by Path</li>
                    <li>CDN for older uploads - covered by <a href="https://mangadex.network" target="_blank">MangaDex Network</a></li>
                </ul>
				
				<h3>Why don't we have ads?</h3>
                <p>As previously stated, ads are the absolute last resort. They will not be used as long as server costs can be covered by cryptocurrency donations , and there are enough clients to maintain the MangaDex Network.</p>

				<h3>How you can help</h3>
				<p>Supporters will be getting access to extra settings as an incentive (coming soon).</p>
				<p>You can support us directly by donating cruptocurrency, or indirectly by running a MangaDex@Home client for the MangaDex Network.</p>
				<p>If you would like more information about running a MangaDex@Home client, please see <a href="/md_at_home">this</a> page. This option is open to everyone, even if you have absolutely no experience or knowledge of server management.</p>
				<p>We accept the following cryptocurrency:</p>

				<div class="row mt-3">
					<div class="col-lg-6 text-center mb-3">
						<div class="card">
							<h6 class="card-header"><?= display_fa_icon('bitcoin', 'Bitcoin', '', 'fab') ?> Bitcoin (BTC)</h6>
							<div class="card-body">
								<p><?= WALLET_QR['BTC'][$templateVar['wallet_no']] ?></p>
								<img style="max-width: 100%; " src="/images/crypto/BTC/bitcoin_<?= WALLET_QR['BTC'][$templateVar['wallet_no']] ?>.png" />
							</div>
						</div>
					</div>
					<div class="col-lg-6 text-center mb-3">
						<div class="card">
							<h6 class="card-header"><?= display_fa_icon('ethereum', 'Ethereum', '', 'fab') ?> Ethereum (ETH)</h6>
							<div class="card-body">
								<p><?= WALLET_QR['ETH'][$templateVar['wallet_no_2']] ?></p>
								<img style="max-width: 100%; " src="/images/crypto/ETH/ETH_<?= WALLET_QR['ETH'][$templateVar['wallet_no_2']] ?>.jpg" />
							</div>
						</div>
					</div>
				</div>
            </div>
			
			<div role="tabpanel" class="tab-pane pt-3" id="history">
                <p>You can claim your previous transactions here. Please enter your Bitcoin/Ethereum wallet address or the transaction hash. Confirmed crypto transactions are updated hourly.</p>
                <p>If you are entering your wallet address, then this only needs to be done once (unless you have multiple wallet addresses).</p>
                <p>If you are entering the transaction hash, then you need to do this after each transaction as these are unique hashes.</p>
                <p>If you are having issues with claiming, please email mangadexstaff@gmail.com for assistance.</p>
				
				<?php if ($templateVar['user']->get_paypal()) : ?>
				<div class="table-responsive">
					<table class="table table-striped table-hover table-sm">
						<thead>
							<tr>
								<th>BTC or ETH address/Tx hash/PayPal email</th>
								<th>Type</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($templateVar['user']->get_paypal() as $paypal) : ?>
							<tr>
								<td><?= $paypal['paypal'] ?></td>
								<td><?= $paypal['count'] ? 'Processed' : 'Pending' ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php else : ?>
				<p>No paypal email added. </p>
				<?php endif; ?>	
				
				<?php if ($templateVar['user']->get_transactions()) : ?>
				<div class="table-responsive">
					<table class="table table-striped table-hover table-sm">
						<thead>
							<tr>
								<th width="200px">Date</th>
								<th>PayPal Email</th>
								<th>Transaction ID</th>
								<th>Subscription ID</th>
								<th>Type</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($templateVar['user']->get_transactions() as $transaction) : ?>
							<tr>
								<td><?= $transaction['date'] ?? 'Pending' ?></td>
								<td><?= $transaction['email'] ?></td>
								<td><?= $transaction['transaction_id'] ?? 'Pending' ?></td>
								<td><?= $transaction['subscription_id'] ?></td>
								<td><?= $transaction['subscription_id'] ? 'Subscription' : ($transaction['transaction_id'] ? 'One off' : 'Pending') ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<p>On your bank or Paypal statement, you will see a reference to "Indexx" next to your transaction.</p>
					<p>All transactions should be accounted for. Thank you for your continued support!</p>
				</div>
				<?php else : ?>
				<p>No paypal transactions claimed. </p>
				<?php endif; ?>	
				
				<?php if ($templateVar['user']->get_btc_transactions()) : ?>
				<div class="table-responsive">
					<table class="table table-striped table-hover table-sm">
						<thead>
							<tr>
								<th width="200px">Date</th>
								<th>Sender address</th>
								<th>Transaction hash</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($templateVar['user']->get_btc_transactions() as $transaction) : ?>
							<tr>
								<td><?= date("d-M-Y h:i:s e", $transaction['timestamp']) ?></td>
								<td><?= $transaction['sender_address'] ?></td>
								<td><?= $transaction['hash'] ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<p>All confirmed Bitcoin transactions up to the previous hour should be accounted for. Thank you for your continued support!</p>
				</div>
				<?php else : ?>
				<p>No Bitcoin transactions claimed. </p>
				<?php endif; ?>	
				
				<?php if ($templateVar['user']->get_eth_transactions()) : ?>
				<div class="table-responsive">
					<table class="table table-striped table-hover table-sm">
						<thead>
							<tr>
								<th width="200px">Date</th>
								<th>Sender address</th>
								<th>Transaction hash</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($templateVar['user']->get_eth_transactions() as $transaction) : ?>
							<tr>
								<td><?= date("d-M-Y h:i:s e", $transaction['timestamp']) ?></td>
								<td><?= $transaction['sender_address'] ?></td>
								<td><?= $transaction['hash'] ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<p>All confirmed Ethereum transactions up to the previous hour should be accounted for. Thank you for your continued support!</p>
				</div>
				<?php else : ?>
				<p>No Ethereum transactions claimed. </p>
				<?php endif; ?>	
				
				<!-- container -->
				<div class="mx-auto form-narrow">
					<form method="post" id="claim_transaction_form" class="mt-3 text-center">
						<div class="form-group">
							<label for="id_string" class="sr-only">PayPal email</label>
							<input type="text" class="form-control" id="id_string" name="id_string" placeholder="Wallet address/Transaction hash" required>
						</div>
						<button class="btn btn-secondary" type="submit" id="claim_transaction_button"><?= display_fa_icon('save') ?> Claim</button>
					</form>
				</div><!-- /container -->			
            </div>
			
			<!--
            <div role="tabpanel" class="tab-pane" id="usd"><iframe style="background-color: #fff; border: 0; height: 300px; margin: 0 auto; display: block;" src=""></iframe></div>
            <div role="tabpanel" class="tab-pane" id="gbp"><iframe style="background-color: #fff; border: 0; height: 300px; margin: 0 auto; display: block; " src=""></iframe></div>
            <div role="tabpanel" class="tab-pane" id="eur"><iframe style="background-color: #fff; border: 0; height: 300px; margin: 0 auto; display: block; " src=""></iframe></div>
			-->
        </div>

    </div>

<?php else : ?>

    <p>MangaDex receives millions of visitors each month, and in order to cope with this, our infrastructure spans many servers. We even created our own CDN to cope with the bandwidth load. All of this isn't cheap, but currently we are sustained by the userbase. As long as the monthly costs can be met this way, ads will not be used. Please make an account if you would like to support us!</p>
	
	We accept the following cryptocurrency:

	<div class="row mt-3">
		<div class="col-lg-6 text-center mb-3">
			<div class="card">
				<h6 class="card-header"><?= display_fa_icon('bitcoin', 'Bitcoin', '', 'fab') ?> Bitcoin (BTC)</h6>
				<div class="card-body">
					<p><?= WALLET_QR['BTC'][$templateVar['wallet_no']] ?></p>
					<img style="max-width: 100%; " src="/images/crypto/BTC/bitcoin_<?= WALLET_QR['BTC'][$templateVar['wallet_no']] ?>.png" />
				</div>
			</div>
		</div>
		<div class="col-lg-6 text-center mb-3">
			<div class="card">
				<h6 class="card-header"><?= display_fa_icon('ethereum', 'Ethereum', '', 'fab') ?> Ethereum (ETH)</h6>
				<div class="card-body">
					<p><?= WALLET_QR['ETH'][$templateVar['wallet_no_2']] ?></p>
					<img style="max-width: 100%; " src="/images/crypto/ETH/ETH_<?= WALLET_QR['ETH'][$templateVar['wallet_no_2']] ?>.jpg" />
				</div>
			</div>
		</div>
	</div>

<?php endif; ?>