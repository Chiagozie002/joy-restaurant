/* ===== DEMO DATA — replaced by PHP responses later (see api.js). Same structure is reused by the e-menu & admin. ===== */
const DEMO_MODE = true;           // set to false when the PHP backend exists
const DEMO_PRODUCTS = [
 {id:1,name:"Jollof Rice",description:"Smoky party-style Nigerian jollof with fried plantain.",price:4500,category:"Rice",image:"assets/images/jollof-rice.jpg",available:true},
 {id:2,name:"Fried Rice & Chicken",description:"Colourful fried rice with juicy grilled chicken.",price:5500,category:"Rice",image:"assets/images/fried-rice.jpg",available:true},
 {id:3,name:"Peppered Chicken",description:"Crispy chicken tossed in a spicy pepper sauce.",price:4000,category:"Chicken",image:"assets/images/peppered-chicken.jpg",available:true},
 {id:4,name:"Roasted Fish",description:"Sweet and seasoned fish, grilled to order.",price:3800,category:"Fish",image:"assets/images/roasted-fish.jpg",available:false},
 {id:5,name:"Classic Beef Burger",description:"Beef patty, cheese, fresh veg and house sauce.",price:5000,category:"Fast Food",image:"assets/images/burger.jpg",available:true},
 {id:6,name:"Chicken Shawarma",description:"Wrapped in warm bread with creamy garlic sauce.",price:3500,category:"Fast Food",image:"assets/images/shawarma.jpg",available:true},
 {id:7,name:"Chapman",description:"Chilled Nigerian fruity cocktail, non-alcoholic.",price:2000,category:"Drinks",image:"assets/images/chapman.jpg",available:true},
 {id:8,name:"Zobo Drink",description:"Hibiscus drink with ginger and pineapple.",price:1500,category:"Drinks",image:"assets/images/zobo.jpg",available:true}
];
/* Demo settings: opening hours + reservation limits (backend will supply these later) */
const SETTINGS = {name:"Joy Restaurant",open:"09:00",close:"23:00",maxGuests:20,slotMinutes:30,
 mapSrc:"https://www.google.com/maps?q=Lagos,Nigeria&output=embed",portfolioUrl:"#"};
