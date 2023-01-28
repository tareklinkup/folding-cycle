<style>
	.v-select {
		margin-bottom: 5px;
		float: right;
		min-width: 200px;
	}

	.v-select .dropdown-toggle {
		padding: 0px;
		height: 25px;
	}

	.v-select input[type=search],
	.v-select input[type=search]:focus {
		margin: 0px;
	}

	.v-select .vs__selected-options {
		overflow: hidden;
		flex-wrap: nowrap;
	}

	.v-select .selected-tag {
		margin: 2px 0px;
		white-space: nowrap;
		position: absolute;
		left: 0px;
	}

	.v-select .vs__actions {
		margin-top: -5px;
	}

	.v-select .dropdown-menu {
		width: auto;
		overflow-y: auto;
	}

	#table1 {
		border-collapse: collapse;
		width: 100%;
	}

	#table1 td,
	#table1 th {
		padding: 5px;
		border: 1px solid #909090;
	}

	#table1 th {
		text-align: center;
	}

	#table1 thead {
		background-color: #cbd6e7;
	}
</style>
<div id="profitLoss">
	<div class="row" style="border-bottom: 1px solid #ccc;">
		<div class="col-md-12">
			<form class="form-inline" v-on:submit.prevent="getProfitLoss">
				<div class="form-group">
					<label>Search Type</label>
					<select class="form-control" v-model="searchType" @change="onChangeSearchType" style="padding: 0px;">
						<option value="" selected>Select</option>
						<option value="customer">By Customer</option>
						<option value="product">By Product</option>
						<option value="lot">By Lot</option>
					</select>
				</div>
				<div class="form-group" style="display:none;" v-bind:style="{display: searchType == 'customer' ? '' : 'none'}">
					<label>Customer &nbsp;</label>
					<v-select v-bind:options="customers" v-model="selectedCustomer" label="display_name" placeholder="Select Customer"></v-select>
				</div>
				<div class="form-group" style="display:none;" v-bind:style="{display: searchType == 'product' ? '' : 'none'}">
					<label>Product</label>
					<v-select v-bind:options="products" v-model="selectedProduct" label="display_text"></v-select>
				</div>
				<div class="form-group" style="display:none;" v-bind:style="{display: searchType == 'lot' ? '' : 'none'}">
					<label>Lot No</label>
					<v-select v-bind:options="lots" v-model="selectedLot" label="SaleDetails_LotNo"></v-select>
				</div>

				<div class="form-group">
					<label>Date from </label>
					<input type="date" class="form-control" v-model="filter.dateFrom">
				</div>

				<div class="form-group">
					<label>to </label>
					<input type="date" class="form-control" v-model="filter.dateTo">
				</div>

				<div class="form-group">
					<input type="submit" class="btn btn-info btn-xs" value="Search" style="padding-top:0px;padding-bottom:0px;margin-top:-4px;">
				</div>
			</form>
		</div>
	</div>

	<div class="row" style="display:none;" :style="{display: show_report ? '' : 'none'}">
		<div class="col-md-12" style="margin: 10px 0;">
			<a href="" @click.prevent="print"><i class="fa fa-print"></i> Print</a>
		</div>
		<div class="col-md-12">
			<div class="table-responsive" id="reportTable">
				<table id="table1">
					<thead>
						<tr>
							<th>Product Id</th>
							<th>Product</th>
							<th>Lot No</th>
							<th>Sold Quantity</th>
							<th>Purchase Rate</th>
							<th>Sale Rate</th>
							<th>Purchased Total</th>
							<th>Sold Amount</th>
							<th>Profit/Loss</th>
						</tr>
					</thead>
					<tbody v-for="data in reportData">
						<tr>
							<td colspan="9" style="background-color: #e3eae7;">
								<strong>Invoice: </strong> {{ data.SaleMaster_InvoiceNo }} |
								<strong>Sales Date: </strong> {{ data.SaleMaster_SaleDate }} |
								<strong>Customer: </strong> {{ data.Customer_Name }} |
								<strong>Discount: </strong> {{ data.SaleMaster_TotalDiscountAmount | decimal }} |
								<strong>VAT: </strong> {{ data.SaleMaster_TaxAmount | decimal }} |
								<strong>Transport Cost: </strong> {{ data.SaleMaster_Freight | decimal }}
							</td>
						</tr>
						<tr v-for="product in data.saleDetails">
							<td>{{ product.Product_Code }}</td>
							<td>{{ product.Product_Name }}</td>
							<td>{{ product.SaleDetails_LotNo }}</td>
							<td style="text-align:right;">{{ product.SaleDetails_TotalQuantity }}</td>
							<td style="text-align:right;">{{ product.Purchase_Rate | decimal }}</td>
							<td style="text-align:right;">{{ product.SaleDetails_Rate | decimal }}</td>
							<td style="text-align:right;">{{ product.purchased_amount | decimal }}</td>
							<td style="text-align:right;">{{ product.SaleDetails_TotalAmount | decimal }}</td>
							<td style="text-align:right;">{{ product.profit_loss | decimal }}</td>
						</tr>
						<tr style="background-color: #f0f0f0;font-weight: bold;">
							<td colspan="6" style="text-align:right;">Total</td>
							<td style="text-align:right;">{{ data.saleDetails.reduce((prev, cur) => { return prev + parseFloat(cur.purchased_amount) }, 0) | decimal }}</td>
							<td style="text-align:right;">{{ data.saleDetails.reduce((prev, cur) => { return prev + parseFloat(cur.SaleDetails_TotalAmount) }, 0) | decimal }}</td>
							<td style="text-align:right;">{{ data.saleDetails.reduce((prev, cur) => { return prev + parseFloat(cur.profit_loss) }, 0) | decimal }}</td>
						</tr>
					</tbody>
					<tfoot style="font-weight:bold;background-color:#e9dcdc;">
						<tr>
							<td style="text-align:right;" colspan="6">Total Profit</td>
							<td style="text-align:right;">
								{{
									reportData.reduce((prev, cur) => { return prev + parseFloat(
										cur.saleDetails.reduce((p, c) => { return p + parseFloat(c.purchased_amount) }, 0)
									)}, 0).toFixed(2)
								}}
							</td>
							<td style="text-align:right;">
								{{
									reportData.reduce((prev, cur) => { return prev + parseFloat(
										cur.saleDetails.reduce((p, c) => { return p + parseFloat(c.SaleDetails_TotalAmount) }, 0)
									)}, 0).toFixed(2)
								}}
							</td>
							<td style="text-align:right;">
								{{
									totalProfit = reportData.reduce((prev, cur) => { return prev + parseFloat(
										cur.saleDetails.reduce((p, c) => { return p + parseFloat(c.profit_loss) }, 0)
									)}, 0).toFixed(2)
								}}
							</td>
						</tr>

						<tr>
							<td colspan="6" style="text-align:right;">Other Income (+)</td>
							<td colspan="2"></td>
							<td style="text-align:right;">{{ otherIncome | decimal }}</td>
						</tr>

						<tr>
							<td colspan="6" style="text-align:right;">VAT (+)</td>
							<td colspan="2"></td>
							<td style="text-align:right;">{{ totalVat = reportData.reduce((prev, cur) => { return prev + parseFloat(cur.SaleMaster_TaxAmount) }, 0).toFixed(2) }}</td>
						</tr>

						<tr>
							<td colspan="6" style="text-align:right;">Total Discount (-)</td>
							<td colspan="2"></td>
							<td style="text-align:right;">{{ totalDiscount = reportData.reduce((prev, cur) => { return prev + parseFloat(cur.SaleMaster_TotalDiscountAmount) }, 0).toFixed(2) }}</td>
						</tr>

						<tr>
							<td colspan="6" style="text-align:right;">Total Returned Value (-)</td>
							<td colspan="2"></td>
							<td style="text-align:right;">{{ otherIncomeExpense.returned_amount | decimal }}</td>
						</tr>

						<tr>
							<td colspan="6" style="text-align:right;">Total Damaged (-)</td>
							<td colspan="2"></td>
							<td style="text-align:right;">{{ otherIncomeExpense.damaged_amount | decimal }}</td>
						</tr>

						<tr>
							<td colspan="6" style="text-align:right;">Cash Transaction (-)</td>
							<td colspan="2"></td>
							<td style="text-align:right;">{{ otherIncomeExpense.expense | decimal }}</td>
						</tr>

						<tr>
							<td colspan="6" style="text-align:right;">Employee Payment (-)</td>
							<td colspan="2"></td>
							<td style="text-align:right;">{{ otherIncomeExpense.employee_payment | decimal }}</td>
						</tr>

						<tr>
							<td colspan="6" style="text-align:right;">Profit Distribute (-)</td>
							<td colspan="2"></td>
							<td style="text-align:right;">{{ otherIncomeExpense.profit_distribute | decimal }}</td>
						</tr>

						<tr>
							<td colspan="6" style="text-align:right;">Loan Interest (-)</td>
							<td colspan="2"></td>
							<td style="text-align:right;">{{ otherIncomeExpense.loan_interest | decimal }}</td>
						</tr>

						<tr>
							<td colspan="6" style="text-align:right;">Assets Sales | Profit/Loss (-)</td>
							<td colspan="2"></td>
							<td style="text-align:right;">{{ otherIncomeExpense.assets_sales_profit_loss | decimal }}</td>
						</tr>

						<tr>
							<td colspan="6" style="text-align:right;">Profit</td>
							<td colspan="2"></td>
							<td style="text-align:right;">
								{{ ((parseFloat(totalProfit) + parseFloat(totalVat) + parseFloat(otherIncome)) - 
									(parseFloat(totalDiscount) + parseFloat(otherIncomeExpense.returned_amount) + parseFloat(otherIncomeExpense.damaged_amount) + parseFloat(otherIncomeExpense.expense) + parseFloat(otherIncomeExpense.employee_payment) + parseFloat(otherIncomeExpense.profit_distribute) + parseFloat(otherIncomeExpense.loan_interest) + parseFloat(otherIncomeExpense.assets_sales_profit_loss))).toFixed(2) }}
							</td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#profitLoss',
		data() {
			return {
				filter: {
					product: null,
					customer: null,
					dateFrom: moment().format('YYYY-MM-DD'),
					dateTo: moment().format('YYYY-MM-DD')
				},
				searchType: '',
				products: [],
				selectedProduct: null,
				customers: [],
				selectedCustomer: null,
				lots: [],
				selectedLot: null,
				reportData: [],
				otherIncomeExpense: {
					income: 0,
					expense: 0,
					employee_payment: 0,
					profit_distribute: 0,
					loan_interest: 0,
					assets_sales_profit_loss: 0,
					damaged_amount: 0,
					returned_amount: 0,
					purchase_discount: 0,
					purchase_vat: 0,
					purchase_transport_cost: 0,
				},
				show_report: false,
			}
		},
		filters: {
			decimal(value) {
				return value == null || value == undefined ? '0.00' : parseFloat(value).toFixed(2);
			}
		},
		created() {
			// this.getCustomers();
		},
		computed: {
			totalTransportCost() {
				return this.reportData.reduce((prev, cur) => {
					return prev + parseFloat(cur.SaleMaster_Freight)
				}, 0).toFixed(2);
			},
			otherIncome() {
				return (
					(
						parseFloat(this.totalTransportCost) +
						parseFloat(this.otherIncomeExpense.income) +
						parseFloat(this.otherIncomeExpense.purchase_discount)
					) - (
						parseFloat(this.otherIncomeExpense.purchase_vat) +
						parseFloat(this.otherIncomeExpense.purchase_transport_cost)
					)
				).toFixed(2);
			}
		},
		methods: {
			onChangeSearchType() {
				this.reportData = [];
				if (this.searchType == 'product') {
					this.getProducts();
					this.selectedCustomer = null;
					this.selectedLot = null;
				} else if (this.searchType == 'customer') {
					this.getCustomers();
					this.selectedProduct = null;
					this.selectedLot = null;
				} else if (this.searchType == 'lot') {
					this.getLotNo();
					this.selectedProduct = null;
					this.selectedCustomer = null;
				}
			},
			getCustomers() {
				axios.get('/get_customers').then(res => {
					this.customers = res.data;
				})
			},
			getLotNo() {
				axios.get('/get_sale_lots').then(res => {
					this.lots = res.data;
				})
			},
			getProducts() {
				axios.get('/get_products').then(res => {
					this.products = res.data;
				})
			},

			async getProfitLoss() {
				if (this.searchType == '') {
					alert('Select a search type')
					return
				}

				if (this.selectedCustomer != null) {
					this.filter.customer = this.selectedCustomer.Customer_SlNo;
				} else {
					this.filter.customer = null;
				}

				if (this.selectedProduct != null) {
					this.filter.product = this.selectedProduct.Product_SlNo;
				} else {
					this.filter.product = null;
				}

				if (this.selectedLot != null) {
					this.filter.lotNo = this.selectedLot.SaleDetails_LotNo;
				} else {
					this.filter.lotNo = null;
				}

				this.reportData = await axios.post('/get_profit_loss', this.filter).then(res => {
					if (this.searchType == 'product') {
						return res.data.filter((obj) => {
							return obj.saleDetails.length > 0
						});
					} else {
						if (this.searchType == 'lot') {

							return res.data.filter((obj) => {
								return obj.saleDetails.length > 0
							});
						} else {

							return res.data;
						}
					}
				})

				this.otherIncomeExpense = await axios.post('/get_other_income_expense', this.filter).then(res => {
					return res.data;
				})

				this.show_report = true;

			},

			async print() {
				let customerText = '';
				if (this.selectedCustomer != null) {
					customerText = `
						<strong>Customer Id: </strong> ${this.selectedCustomer.Customer_Code}<br>
						<strong>Name: </strong> ${this.selectedCustomer.Customer_Name}<br>
						<strong>Address: </strong> ${this.selectedCustomer.Customer_Address}<br>
						<strong>Mobile: </strong> ${this.selectedCustomer.Customer_Mobile}
					`;
				}

				let dateText = '';
				if (this.filter.dateFrom != '' && this.filter.dateTo != '') {
					dateText = `
						Statement from <strong>${this.filter.dateFrom}</strong> to <strong>${this.filter.dateTo}</strong>
					`;
				}
				let reportContent = `
					<div class="container">
						<h4 style="text-align:center">Profit/Loss Report</h4 style="text-align:center">
						<div class="row">
							<div class="col-md-6">${customerText}</div>
							<div class="col-md-6 text-right">${dateText}</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#reportTable').innerHTML}
							</div>
						</div>
					</div>
				`;

				var mywindow = window.open('', 'PRINT', `width=${screen.width}, height=${screen.height}`);
				mywindow.document.write(`
					<?php $this->load->view('Administrator/reports/reportHeader.php'); ?>
				`);

				mywindow.document.head.innerHTML += `
					<style>
						#table1{
							border-collapse: collapse;
							width: 100%;
						}

						#table1 td, #table1 th{
							padding: 5px;
							border: 1px solid #909090;
						}

						#table1 th{
							text-align: center;
						}

						#table1 thead{
							background-color: #cbd6e7;
						}
					</style>
				`;
				mywindow.document.body.innerHTML += reportContent;

				mywindow.focus();
				await new Promise(resolve => setTimeout(resolve, 1000));
				mywindow.print();
				mywindow.close();
			}
		}
	})
</script>