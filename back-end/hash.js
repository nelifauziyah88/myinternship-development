//const bcrypt = require('bcryptjs');

//const password = 'lecturer123';

//bcrypt.hash(password, 10).then(hash => {
//    console.log('Password:', password);
//    console.log('Hashed:', hash);
//});

//const bcrypt = require('bcryptjs');

//const password = 'cdcadmin123';

//bcrypt.hash(password, 10).then(hash => {
//    console.log('Password:', password);
//    console.log('Hashed:', hash);
//});

const bcrypt = require('bcryptjs');

const password = 'gilang123';

bcrypt.hash(password, 10).then(hash => {
    console.log('Password:', password);
    console.log('Hashed:', hash);
});
