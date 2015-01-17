function ccType(num) {
        num = num.replace(/[^\d]/g, '');
        // only consider the first 6 digits to match
        num = num.slice(0,6);

        
        if (num.match(/^5[1-5][0-9]{4}$/)) {
                return 'MasterCard';
        } else if (num.match(/^4[0-9]{5}(?:[0-9]{3})?$/)) {
                return 'Visa';
        } else if (num.match(/^(6011\d{2}|65\d{4}|64[4-9]\d{3}|622(1(2[6-9]|[3-9]\d)|[2-8]\d{2}|9([01]\d|2[0-5])))$/)) {
            return 'Discover';
        }
        else if (num.match(/^(3[47]\d{4})$/)) {
            return 'AMEX';
        }
        else if (num.match(/^(?:2131|1800|35\d{2})\d{2}$/)) {
            return 'JCB';
        }
        else if (num.match(/^3(?:0[0-5]|[68][0-9])[0-9]{3}$/)) {
            return 'Diners';
        }


        return null;
}

function magentoCcType(name) {

        var mc = {
                Visa: "VI",
                MasterCard: "MC",
                Discover: "DI",
                AMEX: "AE",
                JCB: "JCB",
                Diners: "DIN"
        };
        return mc[name];
}