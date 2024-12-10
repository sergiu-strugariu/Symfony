$.validator.addMethod("phone_ro", function (value, element) {
    return this.optional(element) || /^(\+4|)?(07[0-8][0-9]{7})$/g.test(value);
}, `Numărul de telefon nu este valid`);

$.validator.addMethod("validCIF", function (value, element) {
    if (this.optional(element)) {
        return true;
    }

    if (typeof value !== 'string') {
        return false;
    }

    let cif = value.toUpperCase();
    cif = (cif.indexOf('RO') > -1) ? cif.substring(2) : cif;
    cif = cif.replace(/\s/g, '');
    if (cif.length < 2 || cif.length > 10) {
        return false;
    }
    if (Number.isNaN(parseInt(cif))) {
        return false;
    }
    const testKey = '753217532';
    const controlNumber = parseInt(cif.substring(cif.length - 1));
    cif = cif.substring(0, cif.length - 1);
    while (cif.length !== testKey.length) {
        cif = '0' + cif;
    }
    let sum = 0;
    let i = cif.length;

    while (i--) {
        sum += (parseInt(cif.charAt(i)) * parseInt(testKey.charAt(i)));
    }

    let calculatedControlNumber = sum * 10 % 11;

    if (calculatedControlNumber === 10) {
        calculatedControlNumber = 0;
    }
    return controlNumber === calculatedControlNumber;
}, `Vă rugăm să introduceți un CIF valid`);

$.validator.addMethod("regNumber", function (value, element) {
    return this.optional(element) || /^[JFCjfc][0-9]{2}\/[0-9]+\/(19|20)[0-9]{2}$/g.test(value);
}, `Vă rugăm să introduceți un număr de înregistrare valid`);

$.validator.addMethod("iban", function (value, element) {
    if (this.optional(element)) {
        return true;
    }

    var iban = value.replace(/ /g, "").toUpperCase(),
        ibancheckdigits = "",
        leadingZeroes = true,
        cRest = "",
        cOperator = "",
        countrycode, ibancheck, charAt, cChar, bbanpattern, bbancountrypatterns, ibanregexp, i, p;

    if (iban.length < 5) {
        return false;
    }

    countrycode = iban.substring(0, 2);
    bbancountrypatterns = {
        "RO": "[A-Z]{4}[\\dA-Z]{16}",
    };

    bbanpattern = bbancountrypatterns[countrycode];

    if (typeof bbanpattern !== "undefined") {
        ibanregexp = new RegExp("^[A-Z]{2}\\d{2}" + bbanpattern + "$", "");
        if (!ibanregexp.test(iban)) {
            return false;
        }
    }

    ibancheck = iban.substring(4) + iban.substring(0, 4);
    for (i = 0; i < ibancheck.length; i++) {
        charAt = ibancheck.charAt(i);
        if (charAt !== "0") {
            leadingZeroes = false;
        }
        if (!leadingZeroes) {
            ibancheckdigits += "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ".indexOf(charAt);
        }
    }

    for (p = 0; p < ibancheckdigits.length; p++) {
        cChar = ibancheckdigits.charAt(p);
        cOperator = "" + cRest + "" + cChar;
        cRest = cOperator % 97;
    }
    return cRest === 1;
}, `Vă rugăm să introduceți un IBAN valid`);
  

