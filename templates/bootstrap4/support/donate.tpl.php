<div class="container">
    <div class="row">
        <div class="mr-lg-3 mx-auto">
            <iframe
                    src="https://widget.onramper.com?apiKey=pk_prod_pMyn5TmYbijOwdgRNYSWA3nWovBFCzvfZp5SAJ21ncM0&defaultAmount=50&wallets=BTC:bc1qgjskck9mllt8rrc8yvvv389dryuaasrn6lyhlw,ETH:0x89a1ccb27E0b0925B8AA84C7560fB293ed50E89A&onlyCryptos=BTC,ETH&isAddressEditable=false&color=FF9900"
                    height="660px"
                    width="360px"
                    title="Onramper widget"
                    frameborder="0"
                    allow="accelerometer;
  autoplay; camera; gyroscope; payment"
                    style="box-shadow: 3px 3px 5px 0px
  rgba(0,0,0,0.75);"
            >
                <a href="https://widget.onramper.com" target="_blank">Buy crypto</a>
            </iframe>
        </div>
        <div class="col-sm">
            <h3>
                Cryptocurrency Donations and Onramper
            </h3>
            <p>
                We are currently limited to accepting only cryptocurrency donations out of the interest of maintaining our anonymity.
            </p>
            <p>
                Onramper is a fiat (money) on-ramp (to crypto) solution. They allow you to buy cryptocurrency from third-party fiat-to-crypto gateways they support. These fiat-to-crypto gateways require varying levels of identification from you and take a fee every transaction. You only need to verify your identity once. You can find in-depth information about the gateways here: <a href="https://onramper.com/gateways/">https://onramper.com/gateways/</a>
            </p>
            <p>
                Due to the minimum fees they charge at each gateway, small donations of crypto through Onramper is less than ideal. The optimal transaction to avoid getting overly charged by the minimum fee begins at around $125. Onramper's main benefit is allowing users to directly donate to our crypto wallet without having to create an account on a different website. They usually charge an additional 1% fee, but have waived it for us considering our non-profit status.
            </p>
            <p>
                For people who are familiar with and capable of attaining their crypto elsewhere, here are our wallet addresses.
            </p>
            <h3>Wallet Addresses for non-Onramper donations: </h3>
            <div class="row">
                <div class="col">
                    <div class="card"">
                        <h6 class="card-header"><?= display_fa_icon('bitcoin', 'Bitcoin', '', 'fab') ?> Bitcoin (BTC)</h6>
                        <div class="card-body">
                            <p><?= WALLET_QR['BTC'][$templateVar['wallet_no']] ?></p>
                            <img style="max-width: 100%; " src="/images/crypto/BTC/bitcoin_<?= WALLET_QR['BTC'][$templateVar['wallet_no']] ?>.png" />
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card" >
                        <h6 class="card-header"><?= display_fa_icon('ethereum', 'Ethereum', '', 'fab') ?> Ethereum (ETH)</h6>
                        <div class="card-body">
                            <p><?= WALLET_QR['ETH'][$templateVar['wallet_no_2']] ?></p>
                            <img style="max-width: 100%; " src="/images/crypto/ETH/ETH_<?= WALLET_QR['ETH'][$templateVar['wallet_no_2']] ?>.jpg" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>