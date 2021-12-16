const searchButton = document.getElementById('user-search-button');
const exportButton = document.getElementById('export-button');
const container = document.getElementById('view-user-info');


const tableElement = (dataSet) => {
  const userContainer = document.createElement('div');
  userContainer.setAttribute('id', 'user-data-show');

  var table = `<table class="users-table">
  <thead>
      <tr>
        <th scope="row">Apellido</th>
        <th scope="row">Nombre</th>
        <th scope="row">Calle</th>
        <th scope="row">Número</th>
        <th scope="row">CPA</th>
        <th scope="row">Piso</th>
        <th scope="row">Depto</th>
        <th scope="row">Entre</th>
        <th scope="row">Email</th>
        <th scope="row">Provincia</th>
        <th scope="row">Localidad</th>
        <th scope="row">Estado</th>
        <th scope="row">Fecha Registro</th>
        <th scope="row">Suscripcion</th>
        <th scope="row">Método de pago</th>
        <th scope="row">Total pagado</th>
        <th scope="row">CBU</th>
        <th scope="row">DNI</th>
        <th scope="row">CUIL</th>
      </tr>
    </thead>
    <tbody>`;

  dataSet.forEach( (element,index) => {
    //console.log(typeof parseInt(element.cuil))
    var cbu = element.cbu;
    //console.log(cbu);
    const {
      name,
      lastname,
      email,
      subscription,
      active,
      registered,
      address,
      number,
      floor,
      apt,
      city,
      state,
      zip,
      bstreet,
      payment,
      dni,
      cuil,
      amount
    } = element;
    
    table += `<tr>
      <td><input type="hidden" value="${lastname}" name="user[${index}][lastname]">${lastname}</td>
      <td><input type="hidden" value="${name}" name="user[${index}][name]">${name}</td>
      <td><input type="hidden" value="${address}" name="user[${index}][address]">${address}</td>
      <td><input type="hidden" value="${number}" name="user[${index}][number]">${number}</td>
      <td><input type="hidden" value="${zip}" name="user[${index}][zip]">${zip}</td>
      <td><input type="hidden" value="${floor}" name="user[${index}][floor]">${floor}</td>
      <td><input type="hidden" value="${apt}" name="user[${index}][apt]">${apt}</td>
      <td><input type="hidden" value="${bstreet}" name="user[${index}][bstreet]">${bstreet}</td>
      <td><input type="hidden" value="${email}" name="user[${index}][email]">${email}</td>
      <td><input type="hidden" value="${state}" name="user[${index}][state]">${state}</td>
      <td><input type="hidden" value="${city}" name="user[${index}][city]">${city}</td>
      <td><input type="hidden" value="${active}" name="user[${index}][active]">${active}</td>
      <td><input type="hidden" value="${registered}" name="user[${index}][registered]">${registered}</td>
      <td><input type="hidden" value="${subscription}" name="user[${index}][subscription]">${subscription}</td>
      <td><input type="hidden" value="${payment}" name="user[${index}][payment]">${payment}</td>
      <td><input type="hidden" value="${amount}" name="user[${index}][amount]">${amount}</td>
      <td><input type="hidden" value="'${cbu}'" name="user[${index}][cbu]">${cbu}</td>
      <td><input type="hidden" value="${dni}" name="user[${index}][dni]">${dni}</td>
      <td><input type="hidden" value="${cuil}" name="user[${index}][cuil]">${cuil}</td>
    </tr>`;
  });

  table += `</tbody>
  </table>`;

  userContainer.innerHTML = table;
  return userContainer;
};

const tableExport = (data) => {

}

const search = async () => {
  searchButton.addEventListener('click', async (e) => {
    e.preventDefault();

    searchButton.disabled = true;
    
    let error = document.getElementById('user-error');
    let userInfoView = document.getElementById('user-info');
    let email = document.getElementById('search-user-email');
    
    let before = document.getElementById('date-before');
    let after = document.getElementById('date-after');

    const search = await postData(users_vars.search, {
      email: email.value,
      before: before.value,
      after: after.value
    });

    if (!search.success) {
      container.classList.contains('user-success') &&
        container.classList.remove('user-success');

      email.value = '';
      searchButton.disabled = false;

      exportButton.style.display = 'none';
      userInfoView.style.display = 'none';

      container.style.display = 'block';
      container.classList.add('user-error');

      error.textContent = search.data;

      return;
    }

 
    container.classList.contains('user-error') &&
      container.classList.remove('user-error');

    error.innerHTML = '';

    container.style.display = 'block';
    container.classList.add('user-success');

    userInfoView.innerHTML = '';
    userInfoView.style.display = 'block';
    exportButton.style.display = 'inline-block';

   // document.getElementById('user_id').value = search.data.ID;
    userInfoView.appendChild(tableElement(search.data));
    searchButton.disabled = false;
    //console.log(search.data);
  });
};

async function postData(url = '', data = {}) {
  const response = await fetch(url, {
    method: 'POST',
    mode: 'same-origin',
    cache: 'no-cache',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
    },
    redirect: 'follow',
    referrerPolicy: 'no-referrer',
    body: JSON.stringify(data),
  });
  return response.json();
}

search();
