<h2>Checkout</h2>

<form method="POST" action="/WEB_GR4/place-order">

    <label>Địa chỉ nhận hàng</label>

    <select name="address_id">

        <?php foreach(($addresses ?? []) as $a): ?>

            <option value="<?= $a['address_id'] ?>">
                <?= $a['full_address'] ?>
            </option>

        <?php endforeach; ?>

    </select>

    <button type="submit">
        Place Order
    </button>

</form>