<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.3.4/gsap.min.js"></script>
<style>
@import url("https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap");
* {
  font-family: "Roboto", sans-serif;
  font-weight: 400;
  margin: 0px;
  padding: 0px;
  box-sizing: border-box;
  color: #B2B3BB;
}

body {
  background-color: #FF84A2;
  height: 100vh;
}

h1, .totalAmnt, .totalh1, .totalTAmnt {
  color: #3B3E56;
}

.main_container {
  max-width: 500px;
  margin: 50px auto;
  margin-top: 5px;
  perspective: 1000;
  position: relative;
}
.main_container .inspiration {
  position: absolute;
  width: auto;
  height: auto;
  text-transform: uppercase;
  left: -130px;
  top: 80px;
  transform: rotate(-90deg);
}
.main_container .inspiration a {
  font-size: 20px;
  color: #fff;
  letter-spacing: 1px;
  font-weight: 700;
}

.topSection {
  padding: 20px 21px 20px 21px;
  background: #fff;
  padding-top: 21px;
  padding-bottom: 0px;
  position: relative;
  width: 100%;
  height: 180px;
  background: #fff;
  display: grid;
  grid-template-columns: 1fr 1.8fr;
  grid-template-rows: 70px 50px;
  align-items: start;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
  transform: scale(0);
}
.topSection img {
  height: 45px;
  width: 45px;
  background: #fff;
}
.topSection .date {
  height: 45px;
  width: 100%;
  display: grid;
  grid-template-columns: 1fr;
  grid-template-rows: 1fr 1fr;
  opacity: 0;
}
.topSection .date p:nth-child(1) {
  align-self: start;
}
.topSection .date p:nth-child(2) {
  align-self: end;
}
.topSection .name {
  margin-top: 20px;
  height: 48px;
  position: relative;
  grid-area: 2/1/3/3;
}
.topSection .name .vline {
  position: absolute;
  height: 100%;
  width: 5px;
  top: 50%;
  background: #489CF9;
  transform: translateY(-50%);
  left: -8px;
  box-shadow: 2px 0px 4px rgba(72, 156, 249, 0.4);
  opacity: 0;
}
.topSection .name h1 {
  opacity: 0;
  font-size: 20px;
  font-weight: 500;
  margin-bottom: 5px;
}
.topSection .name p {
  opacity: 0;
  margin-top: 20px;
}
.topSection h1.sideDate {
  position: absolute;
  font-size: 14px;
  color: #B2B3BB;
  opacity: 0.6;
  width: 65px;
  height: 16px;
  transform: rotate(-90deg);
  right: -16px;
  top: 43px;
}

.midSection {
  padding: 20px 21px 20px 21px;
  background: #fff;
  background: none;
  padding-top: 0px;
  padding-bottom: 0px;
  height: auto;/*250px;*/
  position: relative;
  transform-origin: center top;
  transform: rotateX(-100deg);
  -webkit-backface-visibility: hidden;
          backface-visibility: hidden;
}
.midSection > * {
  opacity: 0;
}
.midSection:after {
  position: absolute;
  content: "------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------";
  width: 99%;
  letter-spacing: 2px;
  bottom: -5px;
  left: 0px;
  height: 16px;
  font-size: 16px;
  color: #FF84A2;
  overflow: hidden;
}
.midSection h1 {
  text-align: center;
  font-size: 25px;
  font-weight: 500;
  margin-bottom: 5px;
  padding-top: 15px;
}
.midSection ul {
  margin-top: 20px;
  width: 100%;
  min-width: 0px;
  min-height: 0px;
  list-style-type: none;
  display: grid;
  grid-template-columns: 1fr;
  grid-auto-rows: 50px;
  opacity: 1;
}
.midSection ul li {
  position: relative;
  width: 100%;
  overflow: hidden;
  min-width: 0;
  min-height: 0;
  display: grid;
  grid-template-columns: 10px 1fr 190px;
  grid-column-gap: 18px;
  align-items: center;
  overflow-x: hidden;
  margin-left: -10px;
  opacity: 0;
}
.midSection ul li:after {
  position: absolute;
  content: "------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------";
  width: 110%;
  letter-spacing: 2.5px;
  bottom: -3px;
  left: 0px;
  height: 16px;
  font-size: 16px;
}
.midSection ul li * > {
  overflow: hidden;
}
.midSection ul li .listNo {
  margin-left: -15px;
  opacity: 0;
}
.midSection ul li .desc {
  margin-left: -15px;
  opacity: 0;
}
.midSection ul li .totalAmnt, .midSection ul li .totalTAmnt {
  justify-self: end;
  color: #3B3E56;
  font-weight: 700;
  padding-right: 15px;
  opacity: 0;
}
.midSection ul li .totalh1 {
  grid-area: 1/1/2/3;
  text-transform: uppercase;
  text-align: start;
  font-size: 16px;
  font-weight: bold;
  opacity: 0;
}
.midSection ul li .totalTAmnt {
  padding-right: 0px;
  opacity: 0;
}
.midSection ul li.dotend:after {
  display: none;
}
.midSection ul li.listTotal {
  position: relative;
  overflow: visible;
  margin-left: 0px;
  margin-top: 20px;
  opacity: 0;
}
.midSection ul li.listTotal:after {
  content: " ";
  top: -4px;
  height: 4px;
  width: 100%;
  background: #F2DE83;
  z-index: 99;
}
.midSection ul li.listTotal .GTotal {
  margin-top: 20px;
}
.midSection .cornerM {
  position: absolute;
  bottom: -3px;
  height: 6px;
  width: 6px;
  background: #FF84A2;
  z-index: 9999;
  opacity: 1;
}
.midSection .MLeft {
  left: 2px;
}
.midSection .MLeft:after {
  content: "";
  position: absolute;
  height: 6px;
  width: 25px;
  top: 0px;
  background: #FF84A2;
  transform: rotate(16deg);
  transform-origin: top right;
  left: -20px;
  z-index: 9;
}
.midSection .MRight {
  right: 2px !important;
}
.midSection .MRight:after {
  content: "";
  position: absolute;
  height: 6px;
  width: 25px;
  top: 0px;
  background: #FF84A2;
  transform: rotate(-16deg);
  transform-origin: top left;
  right: -20px;
  z-index: 9;
}

.bottomSection {
  padding: 20px 21px 20px 21px;
  background: #fff;
  padding-top: 0px;
  padding-bottom: 0px;
  background: none;
  border-bottom-left-radius: 8px;
  border-bottom-right-radius: 8px;
  position: relative;
  width: 100%;
  height: auto;
  transform-origin: center top;
  -webkit-backface-visibility: hidden;
          backface-visibility: hidden;
  transform: rotateX(-100deg);
}
.bottomSection .cornerB {
  position: absolute;
  top: 0px;
  height: 2px;
  width: 6px;
  background: #FF84A2;
  z-index: 9999;
}
.bottomSection .BLeft {
  left: 2px;
}
.bottomSection .BLeft:after {
  content: "";
  position: absolute;
  height: 5px;
  width: 25px;
  bottom: 0px;
  background: #FF84A2;
  transform: rotate(-16deg);
  transform-origin: bottom right;
  left: -20px;
}
.bottomSection .BRight {
  right: 2px;
}
.bottomSection .BRight:after {
  content: "";
  position: absolute;
  height: 5px;
  width: 25px;
  bottom: 0px;
  background: #FF84A2;
  transform: rotate(16deg);
  transform-origin: bottom left;
  right: -20px;
}
.bottomSection .barline {
  height: 96px;
  width: 100%;
  display: flex;
  overflow: hidden;
  flex-direction: rows;
}
.bottomSection .barline svg {
  fill: #3B3E56;
  color: #3B3E56;
  height: 100%;
  width: 100px;
}

@media only screen and (min-width: 480px) {
  .main_container {
    
    max-width: 500px;
  }

  .topSection {
    padding: 20px 31px 20px 31px;
    padding-top: 31px;
    padding-bottom: 0px;
    position: relative;
    width: 100%;
    height: 190px;
    display: grid;
    grid-template-columns: 1fr 1.8fr;
    grid-template-rows: 80px 50px;
    align-items: start;
    transform: scale(0);
  }
  .topSection .date {
    opacity: 0;
  }
  .topSection .name {
    margin-top: 20px;
    height: 48px;
    position: relative;
    grid-area: 2/1/3/3;
  }
  .topSection .name .vline {
    opacity: 0;
  }
  .topSection h1.sideDate {
    top: 53px;
    right: -12px;
  }

  .midSection {
    padding: 20px 31px 20px 31px;
    background: none;
    padding-top: 15px;
    padding-bottom: 10px;
    height: auto;/*270px;*/
    
    position: relative;
  }
  .midSection ul {
    margin-top: 20px;
    width: 100%;
    min-width: 0px;
    min-height: 0px;
    list-style-type: none;
    display: grid;
    grid-template-columns: 1fr;
    grid-auto-rows: 50px;
    opacity: 1;
  }
  .midSection ul li {
    position: relative;
    width: 100%;
    overflow: hidden;
    min-width: 0;
    min-height: 0;
    display: grid;
    grid-template-columns: 10px 1fr 190px;
    grid-column-gap: 18px;
    align-items: center;
    overflow-x: hidden;
    margin-left: -10px;
    opacity: 0;
  }
  .midSection ul li:after {
    position: absolute;
    content: "------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------";
    width: 110%;
    letter-spacing: 2.5px;
    bottom: -3px;
    left: 0px;
    height: 16px;
    font-size: 16px;
  }
  .midSection ul li * > {
    overflow: hidden;
  }
  .midSection ul li .listNo {
    margin-left: -15px;
    opacity: 0;
  }
  .midSection ul li .desc {
    margin-left: -15px;
    opacity: 0;
  }
  .midSection ul li .totalAmnt, .midSection ul li .totalTAmnt {
    justify-self: end;
    color: #3B3E56;
    font-weight: 700;
    padding-right: 15px;
    opacity: 0;
  }
  .midSection ul li .totalh1 {
    grid-area: 1/1/2/3;
    text-transform: uppercase;
    text-align: start;
    font-size: 16px;
    font-weight: bold;
    opacity: 0;
  }
  .midSection ul li .totalTAmnt {
    padding-right: 0px;
    opacity: 0;
  }
  .midSection ul li.dotend:after {
    display: none;
  }
  .midSection ul li.listTotal {
    position: relative;
    overflow: visible;
    margin-left: 0px;
    margin-top: 20px;
    opacity: 0;
  }
  .midSection ul li.listTotal:after {
    content: " ";
    top: -4px;
    height: 4px;
    width: 100%;
    background: #F2DE83;
    z-index: 99;
  }
  .midSection ul li.listTotal .GTotal {
    margin-top: 20px;
  }

  .bottomSection {
    padding: 20px 31px 20px 31px;
  }
}
</style>
<script>
/*
	***********
	 *********
	  *******
		 *****
	INSPIRATION
	https://dribbble.com/shots/2738907-PayPal-Email-Receipt?1464263543
	
*/

window.addEventListener("load", () => {
	
	// delay time upon completing first froward animation
	const delay = 1;
	const animStyle = {
		firstLoopLTotal: {
			marginTop: 0, 
			ease: "ease-in", 
			duration: 0.3, 
			opacity:1
		},
		secondLoopLTotal: {
			marginLeft: -10, 
			duration: 0.5, 
			ease: "ease-in", 
			opacity: 0,
		},
		firstLoopGTotal: {
			marginTop: 0, 
			ease: "ease-in", 
			duration: 0.3, 
			opacity:1, 
			stagger: {
				each: 0.25
			}
		},
		secondLoopGTotal: {
			paddingRight: 15, 
			ease: "ease-in", 
			duration: 0.3,
			opacity:0, 
			stagger: {
				each: 0.25,
				from: "end"
			}
		}
	}
		 
	// Gasp and options
	let tl_1 = gsap.timeline({
			onReverseComplete: () => {
				setTimeout(() => {
					tl_1.clear();
					tl_1.play();
					timeline_1();
				}, delay*600)
			}
		});
	let tl_2 = gsap.timeline({
		onComplete: () => { 
			setTimeout(timeline_3, delay*1000)
			setTimeout(() => {
				tl_2.clear();
				tl_1.remove(tl_2).reverse(); 
			}, (delay*1000)+0.1) 
		}
	})
	let tl_3 = gsap.timeline({})
	let tl_4 = gsap.timeline();
	
	function timeline_2(){
		return tl_2.to(".listTotal", {...animStyle.firstLoopLTotal}) 
			.to(".GTotal", {...animStyle.firstLoopGTotal}, ">-0.3")
}
	function timeline_3(){
			return 	tl_3.to(".GTotal", {...animStyle.secondLoopGTotal})
									.to(".listTotal", {...animStyle.secondLoopLTotal}, ">-0.3") 
		}
	function timeline_4(){ 
		return tl_4.to(".bottomSection", {transform: "rotateX(25deg)", duration: 0.3, ease: "ease-in"})
	}
	function timeline_1(){
		return tl_1.set(".listTotal", {opacity: 0, marginTop:20, marginLeft: 0})
							.set(".GTotal", {opacity: 0, marginTop:20, paddingRight: 0})
							.to(".topSection", {scale: 1, duration: 0.5, ease: "ease-in"})
							.to(".date", {opacity: 1, duration: 1.5, ease: "ease-in"}, 0)  
							.to(".sideDate", {opacity: 0.6, duration: 1.5, ease: "ease-in"}, 0)  
							.to(".name", {duration: 0.25, marginTop: 0 , ease: "ease-in"}, 0.8)
							.to(".name h1", {duration: 1, opacity: 1, ease: "ease-in"}, 0.8) 
							//---- lablel common-vline_.namep	
							.add("vlineNamep", ">-0.8")
							.to(".name p", {duration: 1, opacity: 1, marginTop: 0, ease: "ease-in"}, "vlineNamep")
							.to(".vline", {duration: 0.5, left: () => {
								const width = window.innerWidth;
								if(width <= 480){
									return -21
								}else {
									return -31
								}
							}, opacity: 1, ease: "ease-in"}, "vlineNamep")  
							//-- second Section
							.to(".midSection", {backgroundColor: "#fff", duration: 0.5, ease: "none"}, "vlineNamep-=1")         
							.to(".midSection", {transform: "rotateX(0deg)", delay: 0, duration: 0.3, ease: "ease-in"}, "vlineNamep+=0.8")
							// -- LABEL -- startforBottomSection	
							.add("startforBottom", ">")
							// -- Contd MidSection
							.to(".midSection h1", {opacity: 1, duration: 0.3, paddingTop: 0, ease: "ease-in"})  

							// -- lists-Label
							.add("commonListAnim", ">")
							.to("ul li.items", {marginLeft: 0, duration: 0.5, ease: "ease-in", opacity: 1, 
														stagger: { 
															each: 0.25   
														}
													 }, "commonListAnim") 
							.to(".listNo",{marginLeft: 0, duration: 0.6, ease: "ease-in", opacity: 1,
															stagger: {
																each: 0.25
														 } 
														},"commonListAnim") 
							.to(".desc", {marginLeft: 0, duration: 0.6, opacity: 1,ease: "ease-in", 
															stagger: {
																each: 0.25
														 } 
														},"commonListAnim+=0.3")
							.to(".totalAmnt", {paddingRight: 0, duration: 0.6, opacity: 1,ease: "ease-in", 
															stagger: {
																each: 0.25
														 },
															onReverseComplete: timeline_4
														},"commonListAnim+=0.6")   
							.add("listTotalStart", ">")
							// --tl_2--
				// 			.add(timeline_2(), "listTotalStart-=0.5")
							// -- bottom Section  
							.to(".bottomSection", {backgroundColor: "#fff", duration: 0},"startforBottom")
							.to(".bottomSection", {transform: "rotateX(0deg)", duration: 0.3, ease: "ease-in"},">")  
	
	}
	
	//starting animation
	timeline_1();
	
})


</script>
<div class="main_container">
  <div class="topSection">
      <img src="{{ asset('assets/images/patrite_logo.png') }}"/>
    <div class="date">
        <?php $date = new DateTime($data->created_at); ?>
      <p>{{ $date->format('d.m.Y') }}</p>
      <p>{{ $data->transaction_id }}</p>
    </div>
    <div class="name">
      <div class="vline"></div>
      <h1>{{ $data->customer_name }}, Hi</h1>
      <!--<p>you've purchased three (3) items in our store</p>-->
    </div>
    <h1 class="sideDate">{{ $date->format('dmY') }}</h1>
  </div>
  <div class="midSection">
    <h1>Receipt</h1>
    <ul> 
      <li class="items">
        <div class="listNo">1</div>
        <div class="desc">Customer Name</div>
        <div class="totalAmnt">{{ $data->customer_name }}</div>
      </li>
      <li class="items">
        <div class="listNo">2</div>
        <div class="desc">Mobile</div>
        <div class="totalAmnt">{{ $data->mobile }}</div>
      </li>
      <li class="items">
        <div class="listNo">3</div>
        <div class="desc">Status</div>
        <div class="totalAmnt">@if($data->status == 1) SUCCESS @elseif($data->status == 2 || $data->status == 3) FAILED @else PROCCESING @endif </div>
      </li>
      <li class="items">
        <div class="listNo">4</div>
        <div class="desc">Transaction ID</div>
        <div class="totalAmnt">{{ $data->transaction_id }} </div>
      </li>
      <li class="items">
        <div class="listNo">5</div>
        <div class="desc">Date & Time</div>
        <div class="totalAmnt">{{ $data->created_at }} </div>
      </li>
      <li class="items">
        <div class="listNo">6</div>
        <div class="desc">Payment Method</div>
        <div class="totalAmnt">Cash </div>
      </li>
      <li class="items">
        <div class="listNo">7</div>
        <div class="desc">Transfer Method</div>
        <div class="totalAmnt">{{ $data->transfer_type }} </div>
      </li>
      <li class="items">
        <div class="listNo">8</div>
        <div class="desc">Amount</div>
        <div class="totalAmnt">{{ $data->amount }} </div>
      </li>
      <li class="items">
        <div class="listNo">9</div>
        <div class="desc">Fee</div>
        <div class="totalAmnt">{{ $data->fee }} </div>
      </li>
      <li class="items">
        <div class="listNo">10</div>
        <div class="desc">Total</div>
        <div class="totalAmnt">{{ $data->amount + $data->fee }} </div>
      </li>
      <li class="items">
        <div class="listNo">11</div>
        <div class="desc">Account Holder</div>
        <div class="totalAmnt">{{ $data->ben_name }} </div>
      </li>
      <li class="items">
        <div class="listNo">12</div>
        <div class="desc">Account No.</div>
        <div class="totalAmnt">{{ $data->ben_ac_number }} </div>
      </li>
      <li class="items">
        <div class="listNo">13</div>
        <div class="desc">IFSC</div>
        <div class="totalAmnt">{{ $data->ben_ac_ifsc }} </div>
      </li>
      <li class="listTotal">
        <div class="totalh1 GTotal">total</div>
        <div class="totalTAmnt GTotal">$95.00</div>
      </li>
    </ul>
    <div class="cornerM MLeft"></div>
    <div class="cornerM MRight"></div>
  </div>
  <div class="bottomSection">
    <div class="cornerB BLeft"></div>
    <div class="cornerB BRight"></div>
    <div class="shapes"></div>
    <div class="barline"><svg enable-background="new 0 0 511.626 511.627" version="1.1" viewBox="0 0 511.63 511.63" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
<rect x="134.76" y="54.816" width="17.699" height="401.71"/>
<rect x="98.786" y="54.816" width="8.848" height="401.71"/>
<rect x="197.57" y="54.816" width="8.852" height="401.71"/>
<rect x="179.58" y="54.816" width="8.852" height="401.71"/>
<rect x="26.84" y="54.816" width="9.136" height="401.71"/>
<rect x="53.959" y="54.816" width="8.851" height="401.71"/>
<rect y="54.816" width="17.987" height="401.99"/>
<rect x="215.56" y="54.816" width="8.852" height="401.71"/>
<rect x="394.86" y="54.816" width="17.986" height="401.71"/>
<rect x="439.97" y="54.816" width="26.837" height="401.71"/>
<rect x="475.65" y="54.816" width="9.134" height="401.71"/>
<rect x="493.64" y="54.816" width="17.986" height="401.99"/>
<rect x="332.04" y="54.816" width="17.987" height="401.71"/>
<rect x="368.02" y="54.816" width="17.987" height="401.71"/>
<rect x="296.07" y="54.816" width="17.986" height="401.71"/>
<rect x="251.24" y="54.816" width="17.989" height="401.71"/>
</svg>
<svg enable-background="new 0 0 511.626 511.627" version="1.1" viewBox="0 0 511.63 511.63" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
<rect x="134.76" y="54.816" width="17.699" height="401.71"/>
<rect x="98.786" y="54.816" width="8.848" height="401.71"/>
<rect x="197.57" y="54.816" width="8.852" height="401.71"/>
<rect x="179.58" y="54.816" width="8.852" height="401.71"/>
<rect x="26.84" y="54.816" width="9.136" height="401.71"/>
<rect x="53.959" y="54.816" width="8.851" height="401.71"/>
<rect y="54.816" width="17.987" height="401.99"/>
<rect x="215.56" y="54.816" width="8.852" height="401.71"/>
<rect x="394.86" y="54.816" width="17.986" height="401.71"/>
<rect x="439.97" y="54.816" width="26.837" height="401.71"/>
<rect x="475.65" y="54.816" width="9.134" height="401.71"/>
<rect x="493.64" y="54.816" width="17.986" height="401.99"/>
<rect x="332.04" y="54.816" width="17.987" height="401.71"/>
<rect x="368.02" y="54.816" width="17.987" height="401.71"/>
<rect x="296.07" y="54.816" width="17.986" height="401.71"/>
<rect x="251.24" y="54.816" width="17.989" height="401.71"/>
</svg>
<svg enable-background="new 0 0 511.626 511.627" version="1.1" viewBox="0 0 511.63 511.63" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
<rect x="134.76" y="54.816" width="17.699" height="401.71"/>
<rect x="98.786" y="54.816" width="8.848" height="401.71"/>
<rect x="197.57" y="54.816" width="8.852" height="401.71"/>
<rect x="179.58" y="54.816" width="8.852" height="401.71"/>
<rect x="26.84" y="54.816" width="9.136" height="401.71"/>
<rect x="53.959" y="54.816" width="8.851" height="401.71"/>
<rect y="54.816" width="17.987" height="401.99"/>
<rect x="215.56" y="54.816" width="8.852" height="401.71"/>
<rect x="394.86" y="54.816" width="17.986" height="401.71"/>
<rect x="439.97" y="54.816" width="26.837" height="401.71"/>
<rect x="475.65" y="54.816" width="9.134" height="401.71"/>
<rect x="493.64" y="54.816" width="17.986" height="401.99"/>
<rect x="332.04" y="54.816" width="17.987" height="401.71"/>
<rect x="368.02" y="54.816" width="17.987" height="401.71"/>
<rect x="296.07" y="54.816" width="17.986" height="401.71"/>
<rect x="251.24" y="54.816" width="17.989" height="401.71"/>
</svg>
<svg enable-background="new 0 0 511.626 511.627" version="1.1" viewBox="0 0 511.63 511.63" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
<rect x="134.76" y="54.816" width="17.699" height="401.71"/>
<rect x="98.786" y="54.816" width="8.848" height="401.71"/>
<rect x="197.57" y="54.816" width="8.852" height="401.71"/>
<rect x="179.58" y="54.816" width="8.852" height="401.71"/>
<rect x="26.84" y="54.816" width="9.136" height="401.71"/>
<rect x="53.959" y="54.816" width="8.851" height="401.71"/>
<rect y="54.816" width="17.987" height="401.99"/>
<rect x="215.56" y="54.816" width="8.852" height="401.71"/>
<rect x="394.86" y="54.816" width="17.986" height="401.71"/>
<rect x="439.97" y="54.816" width="26.837" height="401.71"/>
<rect x="475.65" y="54.816" width="9.134" height="401.71"/>
<rect x="493.64" y="54.816" width="17.986" height="401.99"/>
<rect x="332.04" y="54.816" width="17.987" height="401.71"/>
<rect x="368.02" y="54.816" width="17.987" height="401.71"/>
<rect x="296.07" y="54.816" width="17.986" height="401.71"/>
<rect x="251.24" y="54.816" width="17.989" height="401.71"/>
</svg>
    </div>
  </div>
</div>