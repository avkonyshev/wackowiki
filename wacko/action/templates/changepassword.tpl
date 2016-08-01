
[ === main === ]
	<!--notypo-->
	[= c ChangePassword =
		<form action="[ ' form | ' ]" method="post" name="change_password">
			['' csrf: change_password | '']
			[= secret _ =
				<input type="hidden" name="secret_code" value="[ ' code | e attr ' ]" />
			=]
			<div class="cssform">
				<h3>[ ' title | ' ]</h3>
				[= current _ =
					<p>
						<label for="password">[ ' _t: CurrentPassword ' ]:</label>
						<input type="password" id="password" name="password" size="24" />
					</p>
				=]
				<p>
					<label for="new_password">[ ' _t: NewPassword ' ]:</label>
					<input type="password" id="new_password" name="new_password" size="24" />
					[''' complexity | ''']
				</p>
				<p>
					<label for="conf_password">[ ' _t: ConfirmPassword ']:</label>
					<input type="password" id="conf_password" name="conf_password" size="24" />
				</p>
				<p>
					<input type="submit" class="OkBtn" value="[ ' _t: RegistrationButton ' ]" />
				</p>
			</div>
		</form>
	=]
	[= f ForgotPassword =
		<form action="[ ' form | ' ]" method="post" name="forgot_password">
			['' csrf: forgot_password | '']
			<h3>[ ' format_t: ForgotPassword | ' ]</h3>
			<p>[ ' format_t: ForgotPasswordHint | ' ]</p>
			<div class="cssform">
			<p>
				<label for="user_name">[ ' format_t: UserName | ' ]:</label>
				<input type="text" id="user_name" name="user_name" size="25" maxlength="80" /><br />
				<label for="email">[ ' format_t: Email | ' ]:</label>
				<input type="text" id="email" name="email" size="24" />
			</p>
			<p>
				<input type="submit" class="OkBtn" value="[ ' _t: SendButton ' ]" />
			</p>
			</div>
		</form>
	=]
	<!--/notypo-->