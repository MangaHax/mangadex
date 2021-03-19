<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'home') ? 'active' : '' ?>" href="/support/home" aria-haspopup="true" aria-expanded="false">Home</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'donate') ? 'active' : '' ?>" href="/support/donate" aria-haspopup="true" aria-expanded="false">Donate</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'history') ? 'active' : '' ?>" href="/support/history" aria-haspopup="true" aria-expanded="false">Donation Claim/History</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'affiliates') ? 'active' : '' ?>" href="/support/affiliates" aria-haspopup="true" aria-expanded="false">Affiliates</a>
    </li>
</ul>
