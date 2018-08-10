// Price calculator
'use strict';

class PriceCalculator {
    constructor($wrapper, $pricePlaceholder, bookingOptions, basePrice) {
        this.$wrapper = $wrapper;
        this.$pricePlaceholder = $pricePlaceholder;
        this.basePrice = basePrice;
        // console.log('base price = ' + this.basePrice);
        this.bookingOptions = bookingOptions;
        // console.log(this.bookingOptions);
        this.attachCalculator();
        this.resetInputs();
    }

    calculateTotalPrice() {
        let self = this;
        let total = 0;

        this.$wrapper.find('input[type="number"]').each((index, element) => {
            // console.log('input value = ' + parseInt($(element).val()));
            if ($(element).val() && parseInt($(element).val()) !== 0) {
                let optionId = $('#heading'+(index+1)).data('key');
                // console.log('option id = ' + optionId);
                let inputValue = parseInt($(element).val());
                let optionPrice = self.calculateOptionPrice(optionId, inputValue);
                // console.log('option price = ' + optionPrice);
                total += optionPrice;
            }
        });

        // console.log('total = ' + total);
        return parseInt(this.basePrice + total);
    }

    calculateOptionPrice(id, quantity) {
        let option = this.bookingOptions[id];
        // console.log(option);
        if (option) {
            return option.price * quantity;
        } else {
            return 0;
        }
    }
    
    attachCalculator() {
        let self = this;
        this.$wrapper.find('input[type="number"]').change(function(e) {
            e.preventDefault();
            self.$pricePlaceholder.text(new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(self.calculateTotalPrice()/100));
        });
    }

    resetInputs() {
        this.$wrapper.find('input[type="number"]').each((index, element) => { $(element).val(0) });
    }
}