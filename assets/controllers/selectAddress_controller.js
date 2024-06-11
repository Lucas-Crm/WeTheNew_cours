import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.element.querySelectorAll('.dropdown-item')
            .forEach(btn => {
               btn.addEventListener('click',  (e) => {
                   e.preventDefault();
                   this.element.querySelector('#address_address').value = btn.dataset.address
                   this.element.querySelector('#address_zip_code').value = btn.dataset.zipCode
                   this.element.querySelector('#address_city').value = btn.dataset.city
                });
            });
    }
}
