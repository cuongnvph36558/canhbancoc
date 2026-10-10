// Source: https://provinces.open-api.vn/api/v2/?depth=2
import administrativeUnits from './data/vietnam-administrative-units.json';

export function initializeVietnamAddressFields(root = document) {
    root.querySelectorAll('[data-vietnam-address]').forEach((addressFields) => {
        const provinceSelect = addressFields.querySelector('[data-vietnam-province]');
        const wardSelect = addressFields.querySelector('[data-vietnam-ward]');

        if (!provinceSelect || !wardSelect) {
            return;
        }

        const selectedProvince = provinceSelect.dataset.selectedProvince ?? '';
        const selectedWard = wardSelect.dataset.selectedWard ?? '';

        provinceSelect.replaceChildren(new Option('Chọn tỉnh/thành phố', ''));
        administrativeUnits.forEach(({ name }) => {
            provinceSelect.add(new Option(name, name, false, name === selectedProvince));
        });
        provinceSelect.disabled = false;

        const updateWards = (wardToSelect = '') => {
            const province = administrativeUnits.find(({ name }) => name === provinceSelect.value);

            wardSelect.replaceChildren(new Option(
                province ? 'Chọn phường/xã' : 'Chọn tỉnh/thành phố trước',
                '',
            ));

            province?.wards.forEach((ward) => {
                wardSelect.add(new Option(ward, ward, false, ward === wardToSelect));
            });

            wardSelect.disabled = !province;
        };

        provinceSelect.addEventListener('change', () => updateWards());
        updateWards(selectedWard);
    });
}
