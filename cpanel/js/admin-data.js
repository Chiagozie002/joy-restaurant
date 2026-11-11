/* ===== ADMIN DEMO DATA — swap for PHP responses via admin-api.js. Products reuse DEMO_PRODUCTS from ../assets/js/data.js ===== */
const DEMO_RESERVATIONS = [
 {ref:"JR-1001",name:"Ada Obi",email:"ada@example.com",phone:"08012345678",guests:4,date:"2026-11-08",time:"19:00",status:"Pending"},
 {ref:"JR-1002",name:"Tunde Bello",email:"tunde@example.com",phone:"08098765432",guests:2,date:"2026-11-08",time:"20:30",status:"Confirmed"},
 {ref:"JR-1003",name:"Ngozi Eze",email:"ngozi@example.com",phone:"07011223344",guests:6,date:"2026-11-09",time:"18:00",status:"Confirmed"},
 {ref:"JR-1004",name:"Ibrahim Musa",email:"ibrahim@example.com",phone:"09033445566",guests:3,date:"2026-11-10",time:"13:00",status:"Cancelled"},
 {ref:"JR-1005",name:"Sade Cole",email:"sade@example.com",phone:"08155667788",guests:5,date:"2026-11-11",time:"19:30",status:"Pending"}
];
const DEMO_MESSAGES = [
 {id:1,name:"Kemi A.",email:"kemi@example.com",subject:"Birthday booking",message:"Do you do cakes?",date:"2026-11-05",read:false},
 {id:2,name:"Peter N.",email:"peter@example.com",subject:"Great jollof!",message:"Loved the food.",date:"2026-11-04",read:true},
 {id:3,name:"Zainab L.",email:"zainab@example.com",subject:"Catering",message:"Can you cater 80 guests?",date:"2026-11-03",read:false}
];
const DEMO_TRASH = [];   // soft-deleted products (used by the Trash page later)
const DEMO_ADMIN = {name:"Joy Admin",email:"admin@joyrestaurant.com",title:"Restaurant Manager",avatar:""};
const DEMO_TREND = {labels:["Mon","Tue","Wed","Thu","Fri","Sat","Sun"],values:[6,9,7,12,18,24,15]};   // reservations per day
