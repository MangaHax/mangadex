<div>
    <p>You can claim your previous transactions here. Please enter your Bitcoin/Ethereum wallet address or the transaction hash. Confirmed crypto transactions are updated hourly.</p>
    <p>If you are entering your wallet address, then this only needs to be done once (unless you have multiple wallet addresses).</p>
    <p>If you are entering the transaction hash, then you need to do this after each transaction as these are unique hashes.</p>
    <p>If you made a purchase through Onramper, you should be emailed a link that has the <a href="https://dashboard-test.testwyre.com/track/TF_YVUM7MNAPVN">transaction details</a>, you would click more details and copy the blockchain info.</p>
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