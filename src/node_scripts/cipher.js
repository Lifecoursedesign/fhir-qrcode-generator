const crypto = require('crypto');

function encode(s) {
    var dict = {};
    var data = (s + "").split("");
    var out = [];
    var currChar;
    var phrase = data[0];
    var code = 256;
    for (var i=1; i<data.length; i++) {
        currChar=data[i];
        if (dict[phrase + currChar] != null) {
            phrase += currChar;
        }
        else {
            out.push(phrase.length > 1 ? dict[phrase] : phrase.charCodeAt(0));
            dict[phrase + currChar] = code;
            code++;
            phrase=currChar;
        }
    }
    out.push(phrase.length > 1 ? dict[phrase] : phrase.charCodeAt(0));
    for (var i=0; i<out.length; i++) {
        out[i] = String.fromCharCode(out[i]);
    }
    return out.join("");
}

function decode(s) {
    var dict = {};
    var data = (s + "").split("");
    var currChar = data[0];
    var oldPhrase = currChar;
    var out = [currChar];
    var code = 256;
    var phrase;
    for (var i=1; i<data.length; i++) {
        var currCode = data[i].charCodeAt(0);
        if (currCode < 256) {
            phrase = data[i];
        }
        else {
           phrase = dict[currCode] ? dict[currCode] : (oldPhrase + currChar);
        }
        out.push(phrase);
        currChar = phrase.charAt(0);
        dict[code] = oldPhrase + currChar;
        code++;
        oldPhrase = phrase;
    }
    return out.join("");
}

function main () {
    const args = process.argv;
    const str_req = '-p_-K_-iv';
    const str_either = '-enc_-dec';
    if(!args.every(item => str_req.includes(item)) && !args.some(item => str_either.includes(item))) {
        throw Error(`Invalid process arguments. Check {'-enc' : encryption, '-dec' : decryption, '-p' : payload, '-K' : encryption key, '-iv' : initialize vector}`);
    }
    
    var AESCrypt = {};
    AESCrypt.decrypt = function(payload, cryptKey, iv) {
        var decipher = crypto.createDecipheriv('aes-256-cbc', cryptKey, iv);
        return Buffer.concat([
            decipher.update(payload),
            decipher.final()
        ]);
    }
    AESCrypt.encrypt = function(payload, cryptKey, iv) {
        var encipher = crypto.createCipheriv('aes-256-cbc', cryptKey, iv);
        return Buffer.concat([
            encipher.update(payload),
            encipher.final()
        ]);
    }

    const isEncrypt = args.find((arg)=> arg === '-enc' || arg === '-dec')  === '-enc' ? true : false;
    const pFlagIndex = args.findIndex((arg)=> arg === '-p');
    const payload = args[pFlagIndex + 1];

    const cryptKeyIndex = args.findIndex((arg)=> arg === '-K');
    const cryptKey = args[cryptKeyIndex + 1];

    const ivIndex = args.findIndex((arg)=> arg === '-iv');
    const iv = args[ivIndex + 1];
    if(isEncrypt) {
        const result = AESCrypt.encrypt(payload, cryptKey, iv).toString('hex');
        console.log(encode(result));
    } else {
        const enc = decode(payload);
        console.log(enc);
        // console.log(AESCrypt.decrypt(Buffer.from(enc, 'hex'), cryptKey, iv).toString());
    }
}

main();