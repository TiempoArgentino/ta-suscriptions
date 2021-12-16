if (document.getElementById('sincronize_prices')) {
    document.getElementById('sincronize_prices').addEventListener('click', async () => {
        const msg = document.getElementById('messages-prices')
        const sub_id = document.getElementById('subscription_id').value;

        var usersProcessed = 0;

        const formPrices = document.getElementById('prices-update-content');

        formPrices.style.display = 'none';

        msg.style.display = 'block';
        msg.innerHTML = '<span style="margin-top:1%;display:inline-block;padding:.5% 1%; border-radius:5px; background:green;color:white;">Sincronizando, esto puede llevar un tiempo. Gracias por tu paciencia.</span>';


        const prices = await getSubsPrices(sub_id);


        if (!prices.success) {
            msg.innerHTML = `<span style="margin-top:1%;display:inline-block;padding:.5% 1%; border-radius:5px; background:red;color:white;">${prices.data}</span>`;

            setTimeout(() => {
                formPrices.style.display = 'block';
                msg.style.display = 'none';
            }, 2000);

            return;
        }

        if (prices.data.length == 0) {
            console.log('Vacio');
            return;
        }

        for (let id of prices.data) {
            const info = await getSubInfo(id, sub_id);
            console.log(info);

            msg.innerHTML = `<span style="margin-top:1%;display:inline-block;padding:.5% 1%; border-radius:5px; background:orange;color:white;">Sincronizando...</span>`;

            usersProcessed++;
            if (usersProcessed == prices.data.length) {
                msg.innerHTML = '<span style="margin-top:1%;display:inline-block;padding:.5% 1%; border-radius:5px; background:green;color:white;">Sincronización completada</span>';
                formPrices.style.display = 'block';

                setTimeout(() => {
                    msg.style.display = 'none';
                }, 3000);
            }
        }
    });
}

async function getSubsPrices(value) {
    try {
        const prices = await fetch(update_prices.getSubsPrices, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                post_id: value,
            })
        });
        const response = await prices.json();
        return response;
    } catch (err) {
        console.log(err);
    }

}

async function getSubInfo(membership_id, subscription_id) {
    try {
        const subInfo = await fetch(update_prices.getSubsInfo, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                membership_id,
                subscription_id
            })
        });
        const response = await subInfo.json();
        return response;
    } catch (error) {
        console.log(error);
    }

}