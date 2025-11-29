var request = require("request");
var options = {
  method: "POST",
  url: "https://backend.myhotel2cloud.com/api/update_device_room_fill_status",
  headers: {},
  formData: {
    room_number: "TNJ000025",
    status: "0",
  },
};
request(options, function (error, response) {
  if (error) throw new Error(error);
  console.log(response.body);
});
