// (function($){
//     /**
//      * TODO: idem a sincronización de Odoo, ver odoo-sync.js y odoo-admin-sync.php
//      */
//     $(document).ready(function(){
//         $('#sync-role-user').on('click', function(){
//             $(this).html('Sincronizando, un momento por favor.');
//             console.log(var_func.syncPost)
//             var ok = true;
//             $.ajax({
//                 type:'POST',
//                 url: var_func.syncPost,
//                 data: {
//                     ok
//                 },
//                 success: function(response){
//                     console.log(response)
//                 },
//                 error: function(error) {
//                     console.log(error)
//                 }
//             });
//         });
//     });
// })(jQuery);

async function postData(url = '', data = {}) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            redirect: 'follow',
            referrerPolicy: 'no-referrer',
            body: JSON.stringify(data)
        });
        return response.json();
    } catch (err) {
        console.log(err);
    }
}

async function sync_role_user() {
    if (document.getElementById('sync-role-user')) {
        const button = document.getElementById('sync-role-user');
        var usersProcessed = 0;
        button.addEventListener('click', async () => {
            button.innerHTML = 'Comenzando, un momento por favor...';
            const usersIDs = await fetch(var_func.getUsers);
            let users = await usersIDs.json();
            button.style.display = 'none';
            document.getElementById('loading-spinner').style.display = 'inline-block';
            for (const user of users) {
                const res = await syncFunction(user.ID);
                // console.log(res);
                usersProcessed++;
                if (usersProcessed === users.length) {
                    document.getElementById('loading-spinner').style.display = 'none';
                    button.style.display = 'block';
                    button.innerHTML = 'Terminado';
                    setTimeout(() => {
                        button.innerHTML = 'Sincronizar Roles Usuarios';
                    }, 3000);
                }
            }
        });

    }
}

async function syncFunction(id) {
    const sync = await postData(var_func.syncPost, { id });
    return sync;
}

sync_role_user();